# BookTracker — Система учёта прочитанной литературы

## Стек
- PHP 8.3, Symfony 7 (используется ТОЛЬКО в Infrastructure и Adapters)
- Composer, PSR-4 автолоад (`BookTracker\` → `src/`), PSR-12 код-стайл
- PHPStan level 8, PHPUnit (только для Domain-слоя)

## Архитектура
Гексагональная (Ports & Adapters) + CQRS.

Направление зависимостей: **Domain ← Application ← Infrastructure/Adapters**.
Никогда не нарушать: Domain и Application не импортируют ничего из `Symfony\`.

```
src/
├── Domain/          # Сущности, VO, enum, интерфейсы репозиториев, бизнес-сервисы
├── Application/     # Command/Query хендлеры, DTO, порты импорта/экспорта
├── Adapters/Cli/    # Symfony Console Commands — парсят ввод, вызывают хендлеры
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
- `ReadingEntry`: конструктор **private**, создание только через `ReadingEntry::create(User, Book)`
- Репозитории — только интерфейсы, реализации в Infrastructure
- `RecommendationService` содержит бизнес-правила фильтрации ("не рекомендовать автора после 3 низких оценок", "рекомендовать следующую в серии"), но НЕ обращается к репозиториям напрямую — получает данные через параметры
- Value Objects: `ReadingEntryRating` (валидация диапазона)

#### Глоссарий сущностей
- **Book** — книга в каталоге: title, author, category, complexity. Агрегат. Инварианты: title и author не пустые.
- **User** — пользователь системы: name, email. Агрегат. Инвариант: email уникален.
- **ReadingEntry** — факт чтения книги пользователем: связь User↔Book, status, rating, даты. Отдельный агрегат. Инварианты: создаётся только для существующих User и Book (фабричный метод), рейтинг выставляется только для FINISHED, переходы статусов ограничены (PLANNED→READING→FINISHED, любой→DROPPED, DROPPED→PLANNED).

### Application
- CQRS: Command мутирует состояние, Query читает. Не смешивать.
- Валидация формата — в Command (self-validating), валидация с обращением к репозиторию — в Handler
- `GetRecommendationsHandler` — оркестратор: достаёт данные из репозиториев, передаёт в `RecommendationService`
- `RecommendationDTO` содержит: книгу, score (числовая близость), reason (почему рекомендована)

### Infrastructure
- Репозитории: хранение в JSON-файлах (`storage/books.json`, `storage/users.json`, `storage/reading_entries.json`). При каждом вызове загружают файл, при сохранении — записывают обратно. При необходимости заменяются на Doctrine DBAL/ORM с маппингом в Infrastructure, НЕ в Domain-сущностях
- `BookFeatureVectorizer` implements `VectorizerInterface` — возвращает `BookVector`
- `BookVector` — инфраструктурный объект (массив числовых признаков книги для вычисления расстояния). Живёт в Infrastructure/Vectorization, НЕ в Domain
- `CosineDistance` implements `DistanceMetricInterface`
- Импорт/экспорт: можно использовать `symfony/serializer` в реализациях CsvParser, JsonFormatter и т.д.

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

## Тесты
- PHPUnit, тесты пишутся для Domain и Infrastructure слоёв
- Тесты для Application и Adapters — позже, отдельным этапом
- Структура: `tests/Domain/` повторяет `src/Domain/`, `tests/Infrastructure/` повторяет `src/Infrastructure/`
  - Примеры: `tests/Domain/Enum/ReadingStatusTest.php`, `tests/Infrastructure/Vectorization/CosineDistanceTest.php`
- После каждого изменения: `./vendor/bin/phpunit` + `./vendor/bin/phpstan analyse`