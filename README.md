# Etsetra Services

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
