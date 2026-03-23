# TaskFlow

Полнофункциональный таск-менеджер на Laravel 12 и Vue 3.

## Стек

**Backend**
- PHP 8.5 / Laravel 12
- PostgreSQL 16 (полнотекстовый поиск через `tsvector` + GIN-индекс)
- Redis (очереди + кэш с инвалидацией по тегам)
- Laravel Sanctum (access-токен в JSON + refresh-токен в HttpOnly cookie)
- Laravel Pint (code style) + Larastan level 8 (статический анализ)
- barryvdh/laravel-dompdf (генерация PDF-отчётов)
- Mailpit (перехват почты в dev-окружении)

**Frontend**
- Vue 3 (Composition API, `<script setup>`)
- Vite 7 + @vitejs/plugin-vue 6
- Pinia (хранилища)
- Vue Router 4
- Tailwind CSS 4
- Axios (интерцептор автоматического обновления токена)
- vuedraggable 4 (drag & drop на Kanban-доске)
- @vueuse/core

## Архитектура

```
FormRequest → Controller → Service → Repository → Model
                   ↓            ↓
                Policy       Events / Listeners / Jobs
                   ↓
               Resource
```

Слои строго разделены: контроллер отвечает только за HTTP, сервис — за бизнес-логику, репозиторий — за доступ к данным. DTO переносят провалидированные данные между слоями.

## Функциональность

- **Аутентификация** — регистрация, вход, выход; access-токен живёт 60 минут и автоматически обновляется через HttpOnly cookie с refresh-токеном (30 дней)
- **Проекты** — создание, редактирование, архивирование, удаление; управление участниками с ролями (owner / member)
- **Kanban-доска** — задачи в трёх колонках (Todo / In Progress / Done) с drag & drop
- **Задачи** — полный CRUD, приоритет (low / medium / high), дедлайн, исполнитель, смена статуса
- **Комментарии** — на каждую задачу, с редактированием и удалением
- **Лог активности** — автоматическая лента всех изменений задачи
- **Полнотекстовый поиск** — глобальный поиск по задачам с фильтрами по статусу, приоритету, исполнителю и пагинацией
- **Уведомления** — колокольчик с бейджем непрочитанных, отметка прочитанными по одному и все сразу
- **PDF-отчёты** — асинхронная генерация через очередь, клиентский поллинг и автоскачивание
- **Статистика проекта** — процент выполнения, задачи по статусам и приоритетам, просроченные, топ исполнителей
- **Мои задачи** — все задачи назначенные текущему пользователю, сгруппированные по статусу
- **Уведомления о просрочке** — Artisan-команда по расписанию отправляет уведомления исполнителям просроченных задач
- **Кэширование** — списки проектов и задач кэшируются в Redis с инвалидацией по тегам

## Быстрый старт

### Требования

- Docker + Docker Compose

### 1. Клонировать и настроить

```bash
git clone https://github.com/petrrrnikitin/laravel-taskflow.git
cd laravel-taskflow/taskflow
cp backend/.env.example backend/.env
```

Заполнить `backend/.env` — минимально необходимое:

```env
APP_KEY=          # сгенерировать на шаге 3
DB_DATABASE=taskflow
DB_USERNAME=taskflow
DB_PASSWORD=secret
```

### 2. Собрать и запустить

```bash
make build
make up
```

### 3. Инициализировать приложение

```bash
# Сгенерировать ключ приложения
make artisan key:generate

# Запустить миграции
make migrate

# (опционально) наполнить тестовыми данными
make artisan db:seed
```

### 4. Открыть в браузере

| Сервис | URL |
|---|---|
| Приложение | http://localhost:8000 |
| Mailpit | http://localhost:8025 |
| Swagger (API docs) | http://localhost:8000/api/documentation |

## Make-команды

| Команда | Описание |
| --- | --- |
| `make up` | Запустить все контейнеры |
| `make down` | Остановить все контейнеры |
| `make restart` | Перезапустить контейнеры |
| `make build` | Собрать Docker-образы |
| `make rebuild` | Пересобрать и запустить |
| `make migrate` | Применить миграции |
| `make migration name=<name>` | Создать новую миграцию |
| `make test` | Запустить тесты (SQLite in-memory) |
| `make phpstan` | Статический анализ Larastan (level 8) |
| `make pint` | Исправить code style |
| `make pint-test` | Проверить code style без изменений |
| `make artisan <cmd>` | Выполнить любую Artisan-команду |
| `make ide-helper` | Пересоздать IDE helper-файлы |

## Тесты

```bash
make test
```

Тесты используют SQLite in-memory — отдельная база не нужна.

Запуск конкретного файла:

```bash
make test tests/Feature/TaskTest.php
```

## Статический анализ и стиль кода

```bash
make phpstan
make pint
```
