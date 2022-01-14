# Etsetra Laravel 8+ Services

## Bu kütüphanede **Turkey** için geçerli bazı veri servisleri vardır.

## İçerdiği servisler (1.0.0)
| Servis Adı    | Kaynak                  | Api?  | Key gereksinimi |
|---------------|-------------------------|-------|-----------------|
| Döviz kuru    | freecurrencyapi.net     | Evet  | Evet            |
| Altın kuru    | bigpara.hurriyet.com.tr | Hayır | Hayır           |
| Deprem verisi | koeri.boun.edu.tr       | Hayır | Hayır           |

`Bu servislere sürekli istek atmanız durumunda ip engeli veya istek limitiyle karşılaşabilirsiniz. Bu nedenle bir görev zamanlayarak mümkün olduğunca az sayıda istek gönderin. Zamanladığınız görevin elde ettiği verileri bir yerde saklayarak kullanıcılarınıza kendi veri tabanınızdan servis edin.`

### Kurulum
    composer require etsetra/services

1. Aşağıdaki kodu **config/services.php** dosyasına ekleyin
<pre>
'freecurrencyapi' => [
    'apikey' => env('FREECURRENCYAPI_APIKEY')
],
</pre>

2. **freecurrenkapi.net** üzerinden bir api key alın
<pre>
FREECURRENCYAPI_APIKEY=
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
