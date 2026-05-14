[🇷🇺 Русский](README.ru.md) | [🇬🇧 English](README.md)

# 🚩 Feature Flags for EvolutionCMS CE 3

Manage feature flags with rules engine, statistics, and A/B testing — built for EvolutionCMS CE 3.

## 📦 Installation

```bash
cd /core
php artisan package:installrequire ambrion/evocms-feature-flags "v0.1.0-alpha"
php artisan vendor:publish --provider="EvolutionCMS\FeatureFlags\FeatureFlagsServiceProvider"
php artisan migrate
```

## ⚙️ Requirements

| Requirement         | Version   | Notes                                               |
|---------------------|-----------|-----------------------------------------------------|
| **PHP**             | `^8.3`    | Required for typed properties and readonly classes  |
| **EvolutionCMS CE** | `≥3.1.30` | Tested on v3.1.30; may work on earlier 3.x versions |
| **Composer**        | `^2.0`    | For package installation and dependency management  |

> 💡 **Note**: The module uses modern PHP 8.3 features (`readonly` classes, typed properties, match expressions). PHP 8.1–8.2 are **not supported**.

## ⚙️ Quick Start

1. Open **Manager → Modules → Feature Flags** in EvolutionCMS admin panel
2. Create your first feature flag
3. Use in your snippets:

```php
if ($flags->isEnabled('my_flag', context: ['user_role' => 'manager'])) {
    // show the feature
}
```

## 🎯 Key Features

- ✅ **Rule-based evaluation**: Enable features by user role, document category, date, percentage, and more
- ✅ **A/B Testing**: Split traffic between variants with deterministic user assignment
- ✅ **Statistics & Analytics**: Track flag evaluations, export data, visualize distributions
- ✅ **Admin UI**: Manage flags directly in EvolutionCMS manager — no config file edits needed
- ✅ **TDD-friendly**: Domain-driven design, fully testable without Evo bootstrap

## 🔗 Documentation

- [Feature Flags Core (engine)](https://github.com/Ambrion/feature-flags-core)
- [Author's website](https://ambrion.dev/?site=FeatureFlags)
- [Telegram channel (RU)](https://t.me/ambrion_dev)

## 📬 Support

- 🐛 Bug reports: [GitHub Issues](https://github.com/Ambrion/evocms-feature-flags/issues)
- ✉️ Email: ping@ambrion.dev
- 💬 Telegram: [@ambrion_dev](https://t.me/ambrion_dev)

## 📜 License

MIT © [Ambrion](https://ambrion.dev)

---

> 💡 **Note**: For Russian documentation, see [README.ru.md](README.ru.md).