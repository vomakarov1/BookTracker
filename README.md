# BookTracker

Консольное приложение для учёта прочитанной литературы. Позволяет вести каталог книг, отслеживать прогресс чтения, выставлять оценки и получать персонализированные рекомендации на основе истории чтения.

## Стек

- **PHP 8.4**, **Symfony 7** (Console, Filesystem, Serializer, UID, Lock)
- **PHPUnit** — тесты, **PHPStan level 8** — статический анализ
- Хранилище данных — JSON-файлы (`storage/`)

## Установка

```bash
git clone <repo>
cd BookTracker
composer install
```

## Запуск

```bash
php bin/console <команда> [аргументы] [--опции]
```

---

## Возможности и примеры использования

### Книги

```bash
# Добавить книгу (интерактивный режим — если опции не указаны, будет запрошен ввод)
php bin/console book:create
php bin/console book:create --title="Мастер и Маргарита" --author="Булгаков" --category="Классика" --complexity=5

# Список всех книг
php bin/console book:list

# Просмотр книги по ID
php bin/console book:show <book-id>

# Удалить книгу
php bin/console book:delete <book-id>
```

### Пользователи

```bash
# Добавить пользователя (интерактивный режим)
php bin/console user:create
php bin/console user:create --name="Иван" --email="ivan@example.com"

# Список пользователей
php bin/console user:list

# Просмотр пользователя по ID
php bin/console user:show <user-id>

# Удалить пользователя
php bin/console user:delete <user-id>
```

### Записи о чтении

```bash
# Начать отслеживание книги для пользователя
php bin/console reading:create <user-id> <book-id>

# Список записей пользователя
php bin/console reading:list <user-id>

# Изменить статус (интерактивный выбор, если статус не указан)
php bin/console reading:status <reading-entry-id>
php bin/console reading:status <reading-entry-id> reading

# Выставить оценку прочитанной книге (только для статуса finished, диапазон 1–10)
php bin/console reading:rate <reading-entry-id> 8

# Удалить запись
php bin/console reading:delete <reading-entry-id>
```

#### Допустимые переходы статусов

```
planned → reading → finished
planned → dropped
reading → dropped
dropped → planned
```

### Статистика

```bash
# Статистика чтения по пользователю:
# — количество книг по статусам
# — средний рейтинг по авторам
# — книги завершённые по месяцам
php bin/console stats <user-id>
```

Пример вывода:
```
Books by status
 --------- ------- 
  Status    Count  
 --------- ------- 
  Finished  12     
  Reading   2      
  Planned   7      
 --------- ------- 

Average rating by author
 ------------ ------------ 
  Author       Avg. Rating 
 ------------ ------------ 
  Булгаков     9.0 / 10    
  Достоевский  7.5 / 10    
 ------------ ------------ 
```

### Рекомендации

```bash
# Получить рекомендации книг на основе истории чтения
php bin/console recommend <user-id>
php bin/console recommend <user-id> --limit=10
```

Алгоритм:
- Вычисляет векторное представление каждой книги по признакам (категория, сложность и т.д.)
- Сравнивает кандидатов с книгами, которые пользователь оценил на 7+, через косинусное расстояние
- **Фильтрует**: исключает уже прочитанные книги и авторов с 3 и более низкими оценками (< 3 из 10)

### Импорт и экспорт

```bash
# Импорт книг из файла (поддерживается JSON и CSV)
php bin/console import:books books.json
php bin/console import:books books.csv --format=csv

# Экспорт всех книг в файл
php bin/console export:books output.json
php bin/console export:books output.csv --format=csv
```

Формат CSV:
```csv
title,author,category,complexity
"Война и мир",Толстой,Классика,8
```

Формат JSON:
```json
[
  {"title": "Война и мир", "author": "Толстой", "category": "Классика", "complexity": 8}
]
```

---

## Архитектура

Проект построен на **гексагональной архитектуре (Ports & Adapters)** с **CQRS**.

```
src/
├── Domain/              # Бизнес-ядро — сущности, VO, enum, интерфейсы, доменные сервисы
│   ├── Entity/                     # Агрегаты с бизнес-логикой
│   ├── Enum/                       # Доменные перечисления
│   ├── Exception/                  # Доменные исключения
│   ├── Repository/                 # Интерфейсы репозиториев
│   ├── Service/                    # Доменные сервисы и их интерфейсы
│   └── ValueObject/                # Value Objects
│
├── Application/         # CQRS — Command/Query хендлеры, DTO, порты (интерфейсы)
│   ├── Command/                    # Команды (мутации состояния)
│   │   ├── Book/
│   │   ├── Export/
│   │   ├── Import/
│   │   ├── ReadingEntry/
│   │   └── User/
│   ├── Query/                      # Запросы (только чтение)
│   │   ├── Book/
│   │   ├── ReadingEntry/
│   │   ├── Recommendation/
│   │   ├── Statistics/
│   │   └── User/
│   ├── DTO/                        # Объекты передачи данных между слоями
│   ├── Enum/                       # Перечисления уровня приложения
│   ├── Exception/                  # Исключения уровня приложения
│   └── Port/                       # Интерфейсы-порты (файловый I/O, ID, парсеры)
│
├── Adapters/            # Один класс = одна команда (суффикс CliCommand)
│   └── Cli/                        # CLI-команды
│
└── Infrastructure/      # Репозитории, парсеры, форматтеры, файловый I/O, векторизация
    ├── Export/                     # Реализации форматтеров экспорта
    ├── Import/                     # Реализации парсеров импорта
    ├── Repository/                 # Реализации репозиториев (JSON-файлы)
    ├── Serializer/                 # Фабрика Symfony Serializer
    ├── Storage/                    # Вспомогательный слой работы с JSON-файлами
    └── Vectorization/              # Реализации векторизации и метрики расстояния
```

### Правило зависимостей

```
Domain  ←  Application  ←  Infrastructure
                        ←  Adapters/Cli
```

Domain и Application не импортируют ничего из `Symfony\`. Symfony живёт только в Infrastructure и Adapters.

### Domain

Три агрегата:

| Сущность | Описание |
|---|---|
| `Book` | Книга в каталоге: title, author, category, complexity (1–10) |
| `User` | Пользователь: name, email (уникален) |
| `ReadingEntry` | Факт чтения User↔Book: status, rating (1–10), даты |

Value Objects: `ReadingEntryRating`, `BookComplexity`, `BookVector`.

Доменные сервисы-интерфейсы: `VectorizerInterface`, `DistanceMetricInterface`.

### Application (CQRS)

- **Command** — мутирует состояние, возвращает `void`. Самовалидирующийся (формат полей).
- **Query** — читает данные, не изменяет состояние.
- **Порты** (`Application/Port/`): `IdGeneratorInterface`, `FileReaderInterface`, `FileWriterInterface`, `ImportParserInterface`, `ExportFormatterInterface`.

Генерация ID: в CLI-адаптере для create-команд, в хендлере — для import-команд.

### Infrastructure

| Класс | Роль |
|---|---|
| `JsonBookRepository` / `JsonUserRepository` / `JsonReadingEntryRepository` | Хранение в JSON-файлах |
| `JsonFileStorage` | Вспомогательный слой чтения/записи JSON с блокировками |
| `UuidV4Generator` | Реализует `IdGeneratorInterface` через `symfony/uid` |
| `BookFeatureVectorizer` | Реализует `VectorizerInterface` — признаковый вектор книги |
| `CosineDistance` | Реализует `DistanceMetricInterface` |
| `CsvParser`, `JsonParser` | Реализуют `ImportParserInterface` |
| `CsvFormatter`, `JsonFormatter` | Реализуют `ExportFormatterInterface` |
| `LocalFileReader`, `LocalFileWriter` | Реализуют файловый I/O |

---

## Тесты и статический анализ

```bash
# Запуск тестов
./vendor/bin/phpunit

# Статический анализ (PHPStan level 8)
./vendor/bin/phpstan analyse
```

Тесты покрывают Domain, Application и Infrastructure. Структура `tests/` повторяет `src/`. Для тестов используются in-memory стабы репозиториев (`tests/Stub/`).
