<?php

namespace Etsetra\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use Etsetra\Library\Nokogiri;
use Etsetra\Library\DateTime as DT;

class Api
{
    /**
     * Şehir Listesi
     * Key değerleri Diyanetin kullandığı şehir kodlarıdır.
     * 
     * @param object
     */
    protected $cities;

    public function __construct()
    {
        $this->cities = [
            9146 => 'Adana',
            9158 => 'Adıyaman',
            9167 => 'Afyonkarahisar',
            9185 => 'Ağrı',
            9193 => 'Aksaray',
            9198 => 'Amasya',
            9206 => 'Ankara',
            9225 => 'Antalya',
            9238 => 'Ardahan',
            9246 => 'Artvin',
            9252 => 'Aydın',
            9270 => 'Balıkesir',
            9285 => 'Bartın',
            9288 => 'Batman',
            9295 => 'Bayburt',
            9297 => 'Bilecik',
            9303 => 'Bingöl',
            9311 => 'Bitlis',
            9315 => 'Bolu',
            9327 => 'Burdur',
            9335 => 'Bursa',
            9352 => 'Çanakkale',
            9359 => 'Çankırı',
            9370 => 'Çorum',
            9392 => 'Denizli',
            9402 => 'Diyarbakır',
            9414 => 'Düzce',
            9419 => 'Edirne',
            9432 => 'Elazığ',
            9440 => 'Erzincan',
            9451 => 'Erzurum',
            9470 => 'Eskişehir',
            9479 => 'Gaziantep',
            9494 => 'Giresun',
            9501 => 'Gümüşhane',
            9507 => 'Hakkari',
            20089 => 'Hatay',
            9522 => 'Iğdır',
            9528 => 'Isparta',
            9541 => 'İstanbul',
            9560 => 'İzmir',
            9577 => 'Kahramanmaraş',
            9581 => 'Karabük',
            9587 => 'Karaman',
            9594 => 'Kars',
            9609 => 'Kastamonu',
            9620 => 'Kayseri',
            9629 => 'Kilis',
            9635 => 'Kırıkkale',
            9638 => 'Kırklareli',
            9646 => 'Kırşehir',
            9654 => 'Kocaeli',
            9676 => 'Konya',
            9689 => 'Kütahya',
            9703 => 'Malatya',
            9716 => 'Manisa',
            9726 => 'Mardin',
            9737 => 'Mersin',
            9747 => 'Muğla',
            9755 => 'Muş',
            9760 => 'Nevşehir',
            9766 => 'Niğde',
            9782 => 'Ordu',
            9788 => 'Osmaniye',
            9799 => 'Rize',
            9807 => 'Sakarya',
            9819 => 'Samsun',
            9831 => 'Şanlıurfa',
            9839 => 'Siirt',
            9847 => 'Sinop',
            9854 => 'Şırnak',
            9868 => 'Sivas',
            9879 => 'Tekirdağ',
            9887 => 'Tokat',
            9905 => 'Trabzon',
            9914 => 'Tunceli',
            9919 => 'Uşak',
            9930 => 'Van',
            9935 => 'Yalova',
            9949 => 'Yozgat',
            9955 => 'Zonguldak'
        ];
    }

    /**
     * Kripto ve döviz kurlarını almak için
     * 
     * @param array $symbols
     * @return array
     */
    public function currency(array $symbols)
    {
        $http = Http::get('https://freecurrencyapi.net/api/v2/latest?apikey='.config('services.freecurrencyapi.api_key').'&base_currency=try');

        if ($http->successful())
        {
            if ($data = @$http->json()['data'])
            {
                $data = Arr::where($data, function ($value, $key) use ($symbols) { return Str::contains($key, $symbols); });

                if (in_array('BTC', $symbols))
                {
                    $data['BTC'] = $data['BTC'] / 1000;
                }

                $data = array_map(function($price) { return round(1 / $price, 2); }, $data);

                return $data;
            }
            else
                return $this->log('freecurrencyapi.net/api/v2/latest json formatı değişmiş olabilir.');
        }
        else
            return $this->log('freecurrencyapi.net/api/v2/latest bağlantısı kurulamadı.');
    }

    /**
     * Hürriyet BigPara'dan altın verilerini alır.
     * 
     * @param array $symbols
     * @return array
     */
    public function gold(array $symbols)
    {
        $http = Http::post('https://bigpara.hurriyet.com.tr/altin/');

        if ($http->successful())
        {
            libxml_use_internal_errors(1);

            $dom = new \DOMDocument('1.0', 'UTF-8');
            $dom->preserveWhiteSpace = true;
            $dom->loadHTML($http->body());

            $xpath = new \DOMXpath($dom);
            $jsonScripts = $xpath->query('//*[@id="content"]/div[2]/script[1]/text()');

            if ($script = @$jsonScripts->item(0))
            {
                $slice = json_decode('['.Str::between($script->textContent, '[', ']').']');

                if ($slice)
                {
                    $data = [];

                    foreach ($slice as $item)
                    {
                        $data[$item->Sembol] = [
                            'name' => $item->Adi,
                            'buy' => $item->Alis,
                            'sell' => $item->Satis,
                        ];
                    }

                    $data = Arr::where($data, function ($value, $key) use ($symbols) { return in_array($key, $symbols); });

                    return $data;
                }
                else
                    return $this->log('bigpara.hurriyet.com.tr/altin/ json formatı geçersiz.');
            }
            else
                return $this->log('bigpara.hurriyet.com.tr/altin/ sayfasının yapısı değişmiş olabilir.');
        }
        else
            return $this->log('bigpara.hurriyet.com.tr/altin/ bağlantısı kurulamadı.');
    }

    /**
     * BOUN üzerinden son deprem bilgilerini alır.
     * 
     * @param array $params
     * @return $array
     */
    public function earthquake(array $params = [])
    {
        $http = Http::get('http://www.koeri.boun.edu.tr/scripts/lst0.asp');

        if ($http->successful())
        {
            $body = iconv('ISO-8859-9', 'UTF-8', $http->body());

            preg_match('/<pre>(.*?)<\/pre>/s', $body, $match);

            if ($match = @$match[0])
            {
                $lines = explode(PHP_EOL, $match);

                $keys = [
                    'tarih',
                    'enlem',
                    'boylam',
                    'derinlik',
                    'md',
                    'ml',
                    'mw',
                    'yer',
                    'cozum_niteligi',
                    'diger'
                ];

                $lines = array_map(function($line) use ($params, $keys) {
                    $line = preg_replace(array('/\s{2,}/', '/[\t\n]/'), '_____', $line);

                    $cols = explode('_____', $line);

                    if (count($cols) >= 9)
                    {
                        $arr = [];

                        foreach ($cols as $key => $col)
                        {
                            if (in_array($keys[$key], $params))
                                $arr[$keys[$key]] = $col;
                        }

                        return $arr;
                    }
                }, $lines);

                $lines = array_values(array_filter($lines));

                unset($lines[0]);

                $lines = array_values($lines);

                return $lines;
            }
            else
                return $this->log('koeri.boun.edu.tr dom formatı değişmiş olabilir.');
        }
        else
            return $this->log('koeri.boun.edu.tr bağlantısı kurulamadı.');
    }

    /**
     * Diyanet'ten ezan vakitlerini alır.
     * 
     * @param string $city
     * @return array
     */
    public function ezan(string $city = null)
    {
        if ($city)
        {
            $this->cities = array_filter(
                $this->cities,
                function ($e) use ($city) {
                    return $e == $city;
                }
            );
        }

        $data = [];

        foreach ($this->cities as $code => $city)
        {
            $http = Http::get("https://namazvakitleri.diyanet.gov.tr/tr-TR/$code/xxx");

            if ($http->successful())
            {
                $saw = Nokogiri::fromHtml($http->body());
                $table = $saw->get('#tab-1 .vakit-table')->toArray();

                $tds = data_get($table, '*.tbody.*.tr.*.td');

                foreach ($tds as $td)
                {
                    $date = $td[0]['#text'][0];
                    $date = str_replace([ 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cumartesi', 'Cuma', 'Pazar' ], '', $date);
                    $date = trim($date);
                    $date = str_replace(
                        [ 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık' ],
                        [ '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12' ],
                        $date
                    );
                    $date = (new DT)->createFromFormat('d m Y', $date);
                    $date = date('Y-m-d', strtotime($date));

                    $data[$city][$date] = [
                        'İmsak' => $td[1]['#text'][0],
                        'Güneş' => $td[2]['#text'][0],
                        'Öğle' => $td[3]['#text'][0],
                        'İkindi' => $td[4]['#text'][0],
                        'Akşam' => $td[5]['#text'][0],
                        'Yatsı' => $td[6]['#text'][0],
                    ];
                }
            }
            else
                $data[$city] = $this->log("namazvakitleri.diyanet.gov.tr adresine bağlanılamadı. ($city)");
        }

        return $data;
    }

    /**
     * OpenWeatherMap apilerinden hava durumu bilgisi alır.
     * 
     * @param string $city
     * @return array
     */
    public function weather(string $city = null)
    {
        if ($city)
        {
            $this->cities = array_filter(
                $this->cities,
                function ($e) use ($city) {
                    return $e == $city;
                }
            );
        }

        $data = [];

        foreach ($this->cities as $code => $city)
        {
            $http = Http::get('https://api.openweathermap.org/data/2.5/weather?q='.$city.'&appid='.config('services.openweathermap.api_key'));

            $data[$city] = $http->successful() ? (@$http->json() ?? $this->log("Json formatı geçerli değil. ($city)")) : $this->log("Api bağlantısı kurulamadı. ($city)");
        }

        return $data;
    }

    /**
     * Sağlık bakanlığından covid19 verilerini alır.
     * 
     * @return array
     */
    public function covid19()
    {
        $http = Http::get('https://covid19.saglik.gov.tr/');

        if ($http->successful())
        {
            try
            {
                $daily = json_decode(Str::between($http->body(), 'var sondurumjson = [', '];var'));
                $weekly = json_decode(Str::between($http->body(), 'var haftalikdurumjson = [', '];'));

                return [
                    'daily' => [
                        'date' => $daily->tarih,
                        'test' => str_replace('.', '', $daily->gunluk_test),
                        'case' => str_replace('.', '', $daily->gunluk_vaka),
                        'death' => str_replace('.', '', $daily->gunluk_vefat),
                        'recovered' => str_replace('.', '', $daily->gunluk_iyilesen),
                    ],
                    'weekly' => [
                        'date' => $weekly->tarih,
                        'test' => str_replace('.', '', $weekly->test_sayisi),
                        'case' => str_replace('.', '', $weekly->vaka_sayisi),
                        'patients' => str_replace('.', '', $weekly->hasta_sayisi),
                        'death' => str_replace('.', '', $weekly->vefat_sayisi),
                        'recovered' => str_replace('.', '', $weekly->iyilesen_sayisi),
                    ],
                    'total' => [
                        'case' => str_replace('.', '', $weekly->toplam_vaka_sayisi),
                        'death' => str_replace('.', '', $weekly->toplam_vefat_sayisi),
                        'seriously_patients_avg' => $weekly->ortalama_agir_hasta_sayisi,
                        'pneumonia_rate' => $weekly->hastalarda_zaturre_oran,
                        'bed_occupancy_rate' => $weekly->yatak_doluluk_orani,
                        'intensive_care_occupancy_rate' => $weekly->eriskin_yogun_bakim_doluluk_orani,
                        'ventilator_occupancy_rate' => $weekly->ventilator_doluluk_orani,
                    ]
                ];
            }
            catch (\Exception $e)
            {
                return $this->log('covid19.saglik.gov.tr - '.$e->getMessage());
            }
        }
        else
            return $this->log('covid19.saglik.gov.tr bağlantısı kurulamadı.');
    }

    /**
     * gazeteoku.com'da yayınlanan gazetelerin
     * ilk sayfalarını alır.
     * 
     * @param string $date yyyy-mm-dd
     * @return array
     */
    public function newspapers(string $date)
    {
        $http = Http::get("https://www.gazeteoku.com/gazeteler?date=$date");

        if ($http->successful())
        {
            $saw = Nokogiri::fromHtml($http->body());
            $items = $saw->get('.newspapers > .row > div > a:first-child')->toArray();

            if (count($items))
            {
                $data = array_map(function($item) {
                    return [
                        'name' => $item['title'],
                        'image_src' => 'https://i.gazeteoku.com/storage/'.Str::after($item['img'][0]['data-src'], 'storage/'),
                        'source' => $item['href']
                    ];
                }, $items);

                return $data;
            }
            else
                return $this->log('gazeteoku.com dom formatı değişmiş olabilir.');
        }
        else
            return $this->log('gazeteoku.com bağlantısı kurulamadı.');
    }

    /**
     * Hata mesajlarını log girer
     * 
     * @param string $message
     * @return string
     */
    private function log(string $message)
    {
        Log::channel('services')->critical($message);

        return $message;
    }
}
