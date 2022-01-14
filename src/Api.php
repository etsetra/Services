<?php

namespace Etsetra\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Api
{
    /**
     * Kripto ve döviz kurlarını almak için
     * 
     * @param array $symbols
     * @return array
     */
    public function currency(array $symbols)
    {
        $http = Http::get('https://freecurrencyapi.net/api/v2/latest?apikey='.config('services.freecurrencyapi.apikey').'&base_currency=try');

        if ($http->successful())
        {
            if ($data = @$http->json()['data'])
            {
                $data = Arr::where($data, function ($value, $key) use ($symbols) { return Str::contains($key, $symbols); });

                if ($btc = @$symbols['BTC'])
                    $data['BTC'] = $btc / 1000;

                $data = array_map(function($price) { return round(1 / $price, 2); }, $data);

                return $data;
            }
            else
                return $this->log('freecurrencyapi.net/api/v2/latest sayfasındaki json formatı değişmiş olabilir.');
        }
        else
            return $this->log('freecurrencyapi.net/api/v2/latest sayfasıyla bağlantı kurulamadı.');
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

                    $data = Arr::where($data, function ($value, $key) use ($symbols) { return Str::contains($key, $symbols); });

                    return $data;
                }
                else
                    return $this->log('bigpara.hurriyet.com.tr/altin/ sayfasındaki json formatı geçersiz.');
            }
            else
                return $this->log('bigpara.hurriyet.com.tr/altin/ sayfasının yapısı değişmiş olabilir.');
        }
        else
            return $this->log('bigpara.hurriyet.com.tr/altin/ sayfasıyla bağlantı kurulamadı.');
    }

    /**
     * BOUN üzerinden son deprem bilgilerini alır.
     * 
     * @param array $params
     * @return $array
     */
    public function earthquake(array $params)
    {
        $http = Http::get('http://www.koeri.boun.edu.tr/scripts/lst0.asp');

        if ($http->successful())
        {
            $body = iconv('ISO-8859-9', 'UTF-8', $http->body());

            preg_match('/<pre>(.*?)<\/pre>/s', $body, $match);

            if ($match = @$match[0])
            {
                $lines = explode(PHP_EOL, $match);

                $lines = array_map(function($line) use ($params) {
                    $line = preg_replace(array('/\s{2,}/', '/[\t\n]/'), '_____', $line);
                    $cols = explode('_____', $line);

                    if (count($cols) >= 9)
                    {
                        $arr = [];

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

                        foreach ($cols as $key => $col)
                        {
                            if (@$params[$keys[$key]])
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
                return $this->log('koeri.boun.edu.tr sayfasındaki dom formatı değişmiş olabilir.');
        }
        else
            return $this->log('koeri.boun.edu.tr sayfasıyla bağlantı kurulamadı.');
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
