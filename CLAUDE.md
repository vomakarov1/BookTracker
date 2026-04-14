# BookTracker — Система учёта прочитанной литературы

## Стек
- PHP 8.4, Symfony 7 (используется ТОЛЬКО в Infrastructure и Adapters)
- Composer, PSR-4 автолоад (`BookTracker\` → `src/`), PSR-12 код-стайл
- PHPStan level 8, PHPUnit

## Архитектура
Гексагональная (Ports & Adapters) + CQRS.

Направление зависимостей: **Domain ← Application ← Infrastructure/Adapters**.
Никогда не нарушать: Domain и Application не импортируют ничего из `Symfony\`.

```
src/
├── Domain/          # Сущности, VO, enum, интерфейсы репозиториев, бизнес-сервисы
├── Application/     # Command/Query хендлеры, DTO, порты импорта/экспорта
├── Adapters/Cli/    # Один класс = одна команда, суффикс CliCommand (CreateBookCliCommand и т.д.)
└── Infrastructure/  # Реализации репозиториев, парсеров, форматтеров, векторизации
```

## Граница Symfony
Symfony живёт ТОЛЬКО в Infrastructure и Adapters:
- **Adapters/Cli/**: классы наследуют `Symfony\Component\Console\Command\Command`
- **Infrastructure/**: может использовать Symfony-компоненты (Serializer, Filesystem и т.д.)
- **DI-контейнер**: `services.yaml` связывает интерфейсы с реализациями. Domain и Application получают зависимости через конструктор — они не знают о контейнере
- **Symfony Messenger НЕ используется** — Command/Query Bus реализован вручную или хендлеры вызываются напрямую из CLI-адаптеров

## Ключевые правила

### Domain
- Entity поля — private, изменение только через методы с бизнес-логикой
- `ReadingEntry`: конструктор **private**, создание только через `ReadingEntry::create(string $id, User $user, Book $book, ReadingStatus $status = ReadingStatus::PLANNED)`. Восстановление из хранилища — через `ReadingEntry::reconstruct(string $id, string $userId, string $bookId, ReadingStatus $status, DateTimeImmutable $startedAt, ?ReadingEntryRating $rating = null, ?DateTimeImmutable $finishedAt = null)` — принимает скалярные userId/bookId, не требует объектов User/Book
- Репозитории — только интерфейсы, реализации в Infrastructure. `BookRepositoryInterface` дополнительно имеет `getByIds(array<string> $ids): array<string, Book>` — используется в `GetRecommendationsHandler` для батчевой загрузки книг по ID
- `RecommendationService` содержит бизнес-правила фильтрации ("не рекомендовать автора после 3 низких оценок", "рекомендовать следующую в серии"), но НЕ обращается к репозиториям напрямую — получает данные через параметры. Возвращает `array<RecommendationResult>` (Domain-объект: book, score, reason), который `GetRecommendationsHandler` конвертирует в `RecommendationDTO`
- Value Objects: `ReadingEntryRating` (валидация диапазона 1–10), `BookComplexity` (валидация диапазона 1–10, бросает `InvalidBookException`), `BookVector` (типизированная обёртка над `array<float>`, представляет вектор признаков книги для вычисления сходства)
- Доменные сервисы-интерфейсы: `VectorizerInterface` (преобразует `Book` → `BookVector`), `DistanceMetricInterface` (вычисляет расстояние между двумя `BookVector`). Оба живут в `Domain/Service/` — Domain владеет контрактами, Infrastructure предоставляет реализации.
- `BookVector` живёт в `Domain/ValueObject/` (не в Infrastructure), потому что является типом данных контракта для `VectorizerInterface` и `DistanceMetricInterface`. Перенос в Infrastructure нарушил бы Dependency Rule: Domain-интерфейсы импортировали бы Infrastructure-тип.

#### Глоссарий сущностей
- **Book** — книга в каталоге: title, author, category, complexity. Агрегат. Инварианты: title и author не пустые.
- **User** — пользователь системы: name, email. Агрегат. Инвариант: email уникален.
- **ReadingEntry** — факт чтения книги пользователем: связь User↔Book, status, rating, даты. Отдельный агрегат. Инварианты: создаётся только для существующих User и Book (фабричный метод), рейтинг выставляется только для FINISHED, переходы статусов ограничены (PLANNED→READING, PLANNED→DROPPED, READING→FINISHED, READING→DROPPED, DROPPED→PLANNED; из FINISHED переходов нет).

### Application
- CQRS: Command мутирует состояние (возвращает `void`), Query читает. Не смешивать.
- Валидация формата — в Command (self-validating): обязательные поля, непустые строки. Бизнес-инварианты (диапазоны, правила) — в Domain (Entity / Value Object). Дублировать бизнес-инварианты в Command нельзя — при изменении диапазона достаточно поменять только VO.
- `GetRecommendationsHandler` — оркестратор: достаёт данные из репозиториев, передаёт в `RecommendationService`
- `RecommendationDTO` содержит: книгу, score (числовая близость), reason (почему рекомендована)
- **Генерация ID** — через `Application\Port\IdGeneratorInterface::generate()`. Репозитории НЕ порождают ID. Реализация (`UuidV4Generator`) живёт в Infrastructure и связывается через DI.
  - **Для Create-команд**: ID генерируется в CLI-адаптере (`$id = $this->idGenerator->generate()`), передаётся первым аргументом в Command. Handler принимает ID из Command — он не обращается к `IdGeneratorInterface` напрямую.
  - **Для Import-команд**: ID генерируется внутри Handler — количество сущностей неизвестно до парсинга файла.
- **Файловый I/O** — через `Application\Port\FileReaderInterface::read()` и `Application\Port\FileWriterInterface::write()`. Хендлеры (`ImportBooksHandler`, `ExportBooksHandler`) не вызывают `file_get_contents`/`file_put_contents` напрямую. Реализации (`LocalFileReader`, `LocalFileWriter`) живут в Infrastructure.
- **Импорт/экспорт форматы** — через `Application\Port\ImportParserInterface::parseBooks(string $content): array<BookDTO>` и `Application\Port\ExportFormatterInterface::formatBooks(array<BookDTO> $books): string`. Реализации (`CsvParser`, `JsonParser`, `CsvFormatter`, `JsonFormatter`) живут в Infrastructure. Формат задаётся через `Application\Enum\BookFileFormat` (json, csv).
- **DTO и сборщики** — `BookDTO`, `UserDTO`, `ReadingEntryDTO` используются для передачи данных между слоями. `BookDTOAssembler::fromEntity(Book $book): BookDTO` — статический сборщик в Application.

### Infrastructure
- Репозитории: хранение в JSON-файлах (`storage/books.json`, `storage/users.json`, `storage/reading_entries.json`). При каждом вызове загружают файл, при сохранении — записывают обратно. При необходимости заменяются на Doctrine DBAL/ORM с маппингом в Infrastructure, НЕ в Domain-сущностях
- `UuidV4Generator` implements `IdGeneratorInterface` — генерирует UUID v4 через `Symfony\Component\Uid\Uuid::v4()->toRfc4122()`
- `BookFeatureVectorizer` implements `Domain/Service/VectorizerInterface` — создаёт и возвращает `BookVector`
- `CosineDistance` implements `Domain/Service/DistanceMetricInterface`
- `LocalFileReader` implements `FileReaderInterface`, `LocalFileWriter` implements `FileWriterInterface` — файловый I/O изолирован в Infrastructure
- `CsvParser`, `JsonParser` implements `ImportParserInterface`; `CsvFormatter`, `JsonFormatter` implements `ExportFormatterInterface` — живут в Infrastructure
- Импорт/экспорт: можно использовать `symfony/serializer` в реализациях CsvParser, JsonFormatter и т.д.
- `JsonFileStorage` — вспомогательный класс в `Infrastructure/Storage/`, инкапсулирует чтение/запись JSON-файлов (используется репозиториями). Использует `Symfony\Component\Filesystem\Filesystem` и инициализирует пустой файл `[]` если не существует.

## Исключения

### Domain/Exception/
`BookNotFoundException`, `UserNotFoundException`, `ReadingEntryNotFoundException`, `DuplicateBookException`, `DuplicateUserException`, `DuplicateReadingEntryException`, `InvalidBookException`, `InvalidUserException`, `InvalidRatingException`, `InvalidStatusTransitionException`

### Application/Exception/
`ValidationException` — нарушение формата/обязательных полей (бросается из Command-конструкторов), `ImportFailedException`, `ExportFailedException`

## Конвенции
- Интерфейсы: суффикс `Interface`
- DTO: суффикс `DTO`
- Исключения: суффикс `Exception`
- Каждый Command/Query — отдельный класс, каждый Handler — отдельный класс

## Чего НЕ делать
- Не импортировать `Symfony\` в Domain/ и Application/ — это главный инвариант
- Не использовать Active Record
- Не ставить Doctrine-атрибуты на доменные сущности (маппинг — отдельно в Infrastructure)
- Не добавлять абстрактных классов "на будущее"
- Не добавлять Event/Listener/Subscriber — не нужны (YAGNI)
- Не использовать Symfony Messenger для Command/Query Bus
- Не использовать сервис-локатор / `ContainerAware` — зависимости через конструктор
- Не добавлять `nextId()` или любые методы генерации ID в интерфейсы репозиториев — ID генерируется через `IdGeneratorInterface` в Adapters (для Create-команд) или Application (для Import-команд)

## Тесты
- PHPUnit, тесты пишутся для Domain и Application и Infrastructure слоёв
- Тесты для Adapters пока не пишутся
- Структура: `tests/` повторяет `src/` (например `tests/Domain/Enum/ReadingStatusTest.php`)
- In-memory реализации репозиториев для тестов: `tests/Stub/` (InMemoryBookRepository и т.д.)
- `tests/Stub/InMemoryIdGenerator` — стаб `IdGeneratorInterface`, возвращает последовательные строковые числа ("1", "2", …). Используется в тестах ImportBooksHandler (где handler генерирует ID сам). Create-хендлеры ID не генерируют — тесты передают явный ID в Command.
- `tests/Stub/InMemoryFileReader` — стаб `FileReaderInterface`, хранит файлы в памяти; `addFile(path, content)` регистрирует содержимое, бросает `\RuntimeException` для незарегистрированных путей
- `tests/Stub/InMemoryFileWriter` — стаб `FileWriterInterface`, хранит записанное содержимое в памяти; `getContent(path)` / `hasFile(path)` для проверки в тестах
- `tests/Stub/InMemoryUserRepository` — стаб `UserRepositoryInterface`
- `tests/Stub/InMemoryReadingEntryRepository` — стаб `ReadingEntryRepositoryInterface`
- После каждого изменения: `./vendor/bin/phpunit` + `./vendor/bin/phpstan analyse`