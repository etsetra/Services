# Etsetra Laravel 8+ Services

## Bu kütüphanede **Turkey** için geçerli bazı veri servisleri vardır.

## İçerdiği servisler (1.0.0)
| Servis Adı       | Kaynak                        | Api?  | Key gerekiyor?   |
|------------------|-------------------------------|-------|------------------|
| Döviz kuru       | freecurrencyapi.net           | Evet  | Evet             |
| Altın kuru       | bigpara.hurriyet.com.tr       | Hayır | Hayır            |
| Deprem verisi    | koeri.boun.edu.tr             | Hayır | Hayır            |
| Namaz vakitleri  | namazvakitleri.diyanet.gov.tr | Hayır | Hayır            |
| Hava durumu      | openweathermap.org            | Evet  | Evet             |
| Covid19 verileri | covid19.saglik.gov.tr         | Hayır | Hayır            |
| Gazete sayfaları | gazeteoku.com                 | Hayır | Hayır            |

`Bu servislere sürekli istek atmanız durumunda ip engeli veya istek limitiyle karşılaşabilirsiniz. Bu nedenle bir görev zamanlayarak mümkün olduğunca az sayıda istek gönderin. Zamanladığınız görevin elde ettiği verileri bir yerde saklayarak kullanıcılarınıza kendi veri tabanınızdan servis edin.`

### Kurulum
    composer require etsetra/services

1. Aşağıdaki kodu **config/services.php** dosyasına ekleyin
<pre>
'freecurrencyapi' => [
    'api_key' => env('FREECURRENCYAPI_API_KEY')
],

'openweathermap' => [
    'api_key' => env('OPENWEATHERMAP_API_KEY')
],
</pre>

2. a. **freecurrenkapi.net** üzerinden bir api key alın ve .env dosyasına ekleyin
<pre>
FREECURRENCYAPI_API_KEY=
</pre>
2. b. **openweathermap.org** üzerinden bir api key alın ve .env dosyasına ekleyin
<pre>
OPENWEATHERMAP_API_KEY=
</pre>

3. Problem mesajları için gerekli loglar **storage/logs/services.log** dosyasına yazılır. Bunun için **config/logging.php** dosyasındaki **channels** dizesinin altına şu kodu ekleyin
<pre>
'channels' => [
...
    'services' => [
        'driver' => 'single',
        'path' => storage_path('logs/services.log'),
        'level' => env('LOG_LEVEL', 'debug'),
    ],
...
]
</pre>

## Örnek Kullanım

`Tüm kur verileri TL karşılığıdır.`

### Döviz kuru
<pre>
    use Etsetra\Services\Api;

    // Geçerli birimler: USD, JPY, CNY, CHF, CAD, MXN, INR, BRL, RUB, KRW, IDR, TRY, SAR, SEK, NGN, PLN, ARS, NOK, TWD, IRR, AED, COP, THB, ZAR, DKK, MYR, SGD, ILS, HKD, EGP, PHP, CLP, PKR, IQD, DZD, KZT, QAR, CZK, PEN, RON, VND, BDT, HUF, UAH, AOA, MAD, OMR, CUC, BYR, AZN, LKR, SDG, SYP, MMK, DOP, UZS, KES, GTQ, URY, HRV, MOP, ETB, CRC, TZS, TMT, TND, PAB, LBP, RSD, LYD, GHS, YER, BOB, BHD, CDF, PYG, UGX, SVC, TTD, AFN, NPR, HNL, BIH, BND, ISK, KHR, GEL, MZN, BWP, PGK, JMD, XAF, NAD, ALL, SSP, MUR, MNT, NIO, LAK, MKD, AMD, MGA, XPF, TJS, HTG, BSD, MDL, RWF, KGS, GNF, SRD, SLL, XOF, MWK, FJD, ERN, SZL, GYD, BIF, KYD, MVR, LSL, LRD, CVE, DJF, SCR, SOS, GMD, KMF, STD, XRP, AUD, BGN, BTC, JOD, GBP, ETH, EUR, LTC, NZD

    $currencies = (new Api)->currency([ 'USD', 'EUR', 'BTC' ]);

    Array
    (
        [USD] => 13.54
        [BTC] => 571.43
        [EUR] => 15.49
    )
</pre>

### Altın kuru
<pre>
    use Etsetra\Services\Api;

    // Geçerli birimler: GLDGR, BILEZIKAKAYNAK, XAUUSD, SGLDD, SGLDE, SCUM, SGLDY, SGLDC, SRES, SRESK, GPOR22, GPOR18, GPOR14, GLDZIYNET2_5, GLDZIYNET5LI

    $gold = (new Api)->gold([ 'GLDGR', 'SRES' ]);

    Array
    (
        [GLDGR] => Array
            (
                [name] => ALTIN (TL/GR)
                [buy] => 794.625
                [sell] => 794.786
            )

        [SRES] => Array
            (
                [name] => Reşat Altını
                [buy] => 5310.47
                [sell] => 5389.95
            )

    )
</pre>

### Deprem verisi
<pre>
    use Etsetra\Services\Api;

    // Geçerli parametreler: tarih, enlem, boylam, derinlik, md, ml, mw, yer, cozum_niteligi, diger

    $earthquake = (new Api)->earthquake([
        'tarih',
        'enlem',
        'boylam',
        'derinlik',
        'md',
        'ml',
        'mw',
        'yer',
        'cozum_niteligi',
    ]);

    [0] => Array
        (
            [tarih] => 2022.01.03 18:08:16
            [enlem] => 38.1015
            [boylam] => 30.0237
            [derinlik] => 4.0
            [md] => -.-
            [ml] => 1.7
            [mw] => -.-
            [yer] => BELENPINAR-DINAR (AFYONKARAHISAR)
            [cozum_niteligi] => İlksel
        )

    [1] => Array
        (
            [tarih] => 2022.01.03 17:38:09
            [enlem] => 39.9785
            [boylam] => 26.9085
            [derinlik] => 14.0
            [md] => -.-
            [ml] => 2.5
            [mw] => -.-
            [yer] => ETILI-CAN (CANAKKALE)
            [cozum_niteligi] => İlksel
        )
    ...

</pre>

### Ezan vakitleri
<pre>
    use Etsetra\Services\Api;

    /**
     * - Adını girdiğiniz şehrin gelecek
     * 30 günlük ezan vakitlerini verir.
     *
     * - Parametre boş kalırsa tüm
     * şehirleri verir. (Bu işlem yaklaşık 1 dakika sürer)
     */
    $ezan = (new Api)->ezan('Ankara');

    Array
    (
        [Ankara] => Array
            (
                [2022-01-14] => Array
                    (
                        [İmsak] => 06:33
                        [Güneş] => 08:02
                        [Öğle] => 13:02
                        [İkindi] => 15:31
                        [Akşam] => 17:53
                        [Yatsı] => 19:16
                    )
                [2022-01-15] => Array
                    (
                        [İmsak] => 06:33
                        [Güneş] => 08:02
                        [Öğle] => 13:03
                        [İkindi] => 15:32
                        [Akşam] => 17:54
                        [Yatsı] => 19:17
                    )
                ...
            )
    )
</pre>

### Hava durumu
<pre>
    use Etsetra\Services\Api;

    /**
     * - Adını girdiğiniz şehrin o güne
     * ait hava durumunu verir.
     *
     * - Parametre boş kalırsa tüm
     * şehirleri verir. (Bu işlem yaklaşık 1 dakika sürer)
     */
    $weather = (new Api)->weather('Ankara');

    Array
    (
        [Ankara] => Array
            (
                [coord] => Array
                    (
                        [lon] => 32.8543
                        [lat] => 39.9199
                    )

                [weather] => Array
                    (
                        [0] => Array
                            (
                                [id] => 800
                                [main] => Clear
                                [description] => clear sky
                                [icon] => 01n
                            )

                    )

                [base] => stations
                [main] => Array
                    (
                        [temp] => 268.66
                        [feels_like] => 266.22
                        [temp_min] => 268.66
                        [temp_max] => 268.66
                        [pressure] => 1019
                        [humidity] => 56
                        [sea_level] => 1019
                        [grnd_level] => 913
                    )

                [visibility] => 10000
                [wind] => Array
                    (
                        [speed] => 1.53
                        [deg] => 238
                        [gust] => 1.79
                    )

                [clouds] => Array
                    (
                        [all] => 6
                    )

                [dt] => 1642193630
                [sys] => Array
                    (
                        [type] => 1
                        [id] => 6947
                        [country] => TR
                        [sunrise] => 1642136922
                        [sunset] => 1642171557
                    )

                [timezone] => 10800
                [id] => 323786
                [name] => Ankara
                [cod] => 200
            )
        ...
    )
</pre>

### Covid19
<pre>
    use Etsetra\Services\Api;

    $covid19 = (new Api)->covid19();

    Array
    (
        [daily] => Array
            (
                [date] => 14.01.2022
                [test] => 392438
                [case] => 67857
                [death] => 167
                [recovered] => 56256
            )

        [weekly] => Array
            (
                [date] => 10 - 16 TEMMUZ 2021
                [test] => 1608670
                [case] => 43609
                [patients] => 3748
                [death] => 295
                [recovered] => 36377
            )

        [total] => Array
            (
                [case] => 5514373
                [death] => 50450
                [seriously_patients_avg] => 552
                [pneumonia_rate] => 4.7
                [bed_occupancy_rate] => 48.9
                [intensive_care_occupancy_rate] => 62.1
                [ventilator_occupancy_rate] => 26.3
            )

    )
</pre>

### Gazete sayfaları
<pre>
    use Etsetra\Services\Api;

    $newspapers = (new Api)->newspapers('2022-01-15');

    Array
    (
        [0] => Array
            (
                [name] => Cumhuriyet
                [image_src] => https://i.gazeteoku.com/storage/files/images/2022/01/15/cumhuriyet-2022-01-15-VrdZ.jpg
                [source] => https://www.gazeteoku.com/gazeteler/2022-01-15/cumhuriyet-gazetesi-manseti
            )

        [1] => Array
            (
                [name] => Yeni Şafak
                [image_src] => https://i.gazeteoku.com/storage/files/images/2022/01/15/yeni-safak-2022-01-15-YpiB.jpg
                [source] => https://www.gazeteoku.com/gazeteler/2022-01-15/yeni-safak-gazetesi-manseti
            )
        ...
    )
</pre>
