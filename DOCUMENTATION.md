Общая информация о проэкте.
    Проэкт на ншш2

Документация по довавлению новых партнеров:

    Контроллер - console/controllers/PartnerController

Модели: webmaster/models/partners

    Partner - модель партнеров
    PartnerOffers - модель офферов партнеров
    PartnerOrders - модель ордеров партнеров

В реализации логики бил использован Strategy Pattern

    каталог с логикой - webmaster/modules/api/partners
    контектсный класс - PartnerCRM
    strategy/[Affscale, MyLandCRM, RetailCRM...] - классы стратегии, каждый имеет свою логику передачи лида партнеру

    Довавить нового партнера можно добавив запись в бд, и соответствующий класс в католог стратегий(webmaster/modules/api/partners/strategy)
    контролер вызывается по cron-событию раз в минуту