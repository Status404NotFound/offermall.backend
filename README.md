Crmka CRM 2
================

[Crmka CRM 2] based on Advanced Project Template [Yii 2](http://www.yiiframework.com/) application.

The template includes three tiers: 
**crm, 
callcenter, 
webmaster, 
tds, 
reg_fish_order
and console,** 
each of which is a separate Yii application.

Yii 2 documentation is at [docs/guide/README.md](docs/guide/README.md).

Crmka CRM 2 documentation is at './DOCUMENTATION.md'

DIRECTORY STRUCTURE
-------------------

```
common
    config/              contains shared configurations
    mail/                contains view files for e-mails
    models/              contains model classes used in both backend and frontend
    tests/               contains tests for common classes    
console
    config/              contains console configurations
    controllers/         contains console controllers (commands)
    migrations/          contains database migrations
    models/              contains console-specific model classes
    runtime/             contains files generated during runtime
crm
    assets/              contains application assets such as JavaScript and CSS
    config/              contains crm configurations
    controllers/         contains Web controller classes
    models/              contains crm-specific DB tables model classes
    forms/               contains crm-specific form model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for frontend application
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
    widgets/             contains crm widgets
callcenter
    assets/              contains application assets such as JavaScript and CSS
    config/              contains callcenter configurations
    controllers/         contains Web controller classes
    models/              contains callcenter-specific DB tables model classes
    forms/               contains callcenter-specific form model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for frontend application
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
    widgets/             contains callcenter widgets
webmaster
    assets/              contains application assets such as JavaScript and CSS
    config/              contains webmaster configurations
    controllers/         contains Web controller classes
    models/              contains webmaster-specific DB tables model classes
    forms/               contains webmaster-specific form model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for frontend application
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
    widgets/             contains webmaster widgets
tds
    assets/              contains application assets such as JavaScript and CSS
    config/              contains tds configurations
    controllers/         contains Web controller classes
    models/              contains tds-specific DB tables model classes
    forms/               contains tds-specific form model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for frontend application
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
    widgets/             contains tds widgets
reg_fish_order
    assets/              contains application assets such as JavaScript and CSS
    config/              contains reg_fish_order configurations
    controllers/         contains Web controller classes
    models/              contains reg_fish_order-specific DB tables model classes
    forms/               contains reg_fish_order-specific form model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for frontend application
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
    widgets/             contains reg_fish_order widgets
vendor/                  contains dependent 3rd-party packages
environments/            contains environment-based overrides
```
