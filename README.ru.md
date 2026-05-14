[🇷🇺 Русский](README.ru.md) | [🇬🇧 English](README.md)

# 🚩 Feature Flags для EvolutionCMS CE 3

Управление фич-флагами с правилами, статистикой и A/B-тестированием.

## 📦 Установка

```bash
cd /core
php artisan package:installrequire ambrion/evocms-feature-flags "v0.1.0-alpha"
php artisan vendor:publish --provider="EvolutionCMS\FeatureFlags\FeatureFlagsServiceProvider"
composer dump-autoload
php artisan migrate
```

## ⚙️ Требования

| Требование          | Версия    | Примечание                                                    |
|---------------------|-----------|---------------------------------------------------------------|
| **PHP**             | `^8.3`    | Требуется для typed properties и readonly-классов             |
| **EvolutionCMS CE** | `≥3.1.30` | Протестировано на v3.1.30; может работать на более ранних 3.x |
| **Composer**        | `^2.0`    | Для установки пакета и управления зависимостями               |

> 💡 **Примечание**: Модуль использует возможности современного PHP 8.3 (`readonly`-классы, типизированные свойства, match-выражения). Версии PHP 8.1–8.2 **не поддерживаются**.

## ⚙️ Быстрый старт

1. Откройте **Менеджер → Модули → Feature Flags** в админке EvolutionCMS
2. Создайте первый флаг
3. Используйте в сниппетах:

```php
if ($flags->isEnabled('my_flag', context: ['user_role' => 'manager'])) {
    // показать фичу
}
```

## 🎯 Возможности

- ✅ **Правила оценки**: Включайте фичи по роли пользователя, категории документа, дате, проценту трафика и другим условиям
- ✅ **A/B-тестирование**: Распределяйте трафик между вариантами с детерминированным назначением пользователей
- ✅ **Статистика и аналитика**: Отслеживайте оценки флагов, экспортируйте данные, визуализируйте распределение
- ✅ **Админ-интерфейс**: Управляйте флагами прямо в менеджере EvolutionCMS — без правки конфигов
- ✅ **TDD-friendly**: Доменно-ориентированный дизайн, тестируется без загрузки ядра Evo

## 🔗 Документация

- [Ядро Feature Flags Core](https://github.com/Ambrion/feature-flags-core)
- [Сайт автора](https://ambrion.dev/?site=FeatureFlags)
- [Telegram канал](https://t.me/ambrion_dev)

## 📬 Поддержка

- 🐛 Баги: [GitHub Issues](https://github.com/Ambrion/evocms-feature-flags/issues)
- ✉️ Email: ping@ambrion.dev
- 💬 Telegram: [@ambrion_dev](https://t.me/ambrion_dev)

## 📜 Лицензия

MIT © [Ambrion](https://ambrion.dev)

---

> 💡 **Примечание**: For English documentation, see [README.md](README.md).