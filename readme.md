##Тестовое задание для VR Dev Team

####Использование

Используемые валюты указываются в .env: параметр `APP_SUPPORTED_CURRENCIES`.

Обновелние курсов для используемых валют: `php bin/console app:exchange-rates:update`.

Запуск встроенного сервера: `php bin/console server:start`.


####Описание классов

`App\Interfaces\ExchangeRatesProviderInterface` - интерфейс источника данных о курсах.
- `getLastRates(): ExchangeRatesResponse` - возвращает последние доступные курсы валют;
- `getRatesForDate(\DateTime $date): ExchangeRatesResponse` - возвращает курс валют на указанную дату;

`ExchangeRatesResponse` - нормализованный ответ от API.
- `getDate(): \DateTime` - возвращает дату для которой получены курсы;
- `getPairs(): array` - возвращает массив курсов вида `(string)CURRENCY_CODE => (float)RATE`;

`App\DataProviders\ExchangeRates\ExchangeRatesAPIIO` - реализация интерфейса. Источник данных - http://exchangeratesapi.io

`App\Service\ExchangeRatesService` - сервис для работы с курсами в приложении
- `getLastRates(): ExchnageRate[]` - возвращает последние сохраненные в БД курсы;
- `updateRates(): bool` - обновить курсы в БД. Возвращает `true` в случае успеха и `false` в случае неудачи;