<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Response;

class MyController extends Controller
{
    const ASCII_a = 97;
    const ASCII_z = 122;
    const NUM_WORDLIST_FILES = 24;

//    const SEARCH_MODE_PHP_FILES = 0;
//    const SEARCH_MODE_PHP_PDO = 1;
//    const SEARCH_MODE_PHP_MYSQLI = 2;
//    const SEARCH_MODE_PHP_NATIVE = 3;
//    const SEARCH_MODE_PHP_APC = 4;
//    const SEARCH_MODE_GO_FILES = 5;
//    const SEARCH_MODE_PYTHON_FILES = 6;
//    const SEARCH_MODE_PYTHON_NDB = 7;
//    const SEARCH_MODE_NODEJS_FILES = 8;
//    const SEARCH_MODE_NODEJS_MYSQL = 9;
//    const SEARCH_MODE_NODEJS_MEMCACHIER = 10;
//    const SEARCH_MODE_NODEJS_MONGO = 11;
//    const SEARCH_MODE_MAX_ID = 11;

    const RETURN_TYPE_HTML = 'html';
    const RETURN_TYPE_JSON = 'json';

    private $time_start;
    private $total_compares = 0;
    private $total_found = 0;
    private $letter_counts_arr = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
    private $cache_count = 0;
    private $pubpath = '';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->pubpath = base_path('public');
    }

    /**
     * getHTML
     *
     * @param int $word_len
     * @param array $words_arr
     * @return string
     */
    private function getHTML(int $word_len, array $words_arr): string
    {
        $res = '<p>' . $word_len . '-Letter Words</p><div class="wordcontainer">';
        foreach ($words_arr as $word) {
            if ($word)
                $res .= '<div>' . $word . '</div>';
        }
        $res .= '</div>';
        return $res;
    }

    /**
     * getFooter
     * @return string
     */
    private function getFooter(): string
    {
        $total_time_secs = ceil((microtime(true) - $this->time_start) * 1000) / 1000;

        $res = '';
        if ($this->total_found == 0)
            $res .= '<p class="noresults">No words found.</p>';

        $res .= '<p id="results_footer">';

        $res .= 'Compares: ' . $this->total_compares . '. Results: ' . $this->total_found . ' in ' . $total_time_secs . 's.';
        $res .= ' Memcached: ' . $this->cache_count;
        $res .= '<br />PHP ' . phpversion();

//        switch ($this->search_mode) {
//            case SEARCH_MODE_PHP_FILES: $res .= '<br />PHP '.phpversion();break;
//            case SEARCH_MODE_PHP_PDO: $res .= '<br />PHP 5.5, PDO SQL driver';break;
//            //      case SEARCH_MODE_PHP_APC: $res .= '<br />PHP 5.5, searching Alternative PHP Cache';break;
//        }
        $res .= '</p>';
        return $res;
    }

    /**
     * nullifyUnmatchedWords
     *
     * @param int $word_len
     * @param array $words_arr
     * @return int
     */
    private function nullifyUnmatchedWords(int $word_len, array &$words_arr): int
    {
        $num_found = count($words_arr);
        foreach ($words_arr as $i => $word) {
            $letter_counts_arr = $this->letter_counts_arr; //copy of the array
            for ($j = 0; $j < $word_len; $j++) {
                $this->total_compares++;
                $idx = ord($word[$j]) - self::ASCII_a;
                if ($letter_counts_arr[$idx] == 0) {
                    $words_arr[$i] = NULL;
                    $num_found--;
                    break;
                } else {
                    $letter_counts_arr[$idx] -= 1;
                }
            }
        }
        return $num_found;
    }

    /**
     * nullifyUnmatchedWordsWild
     *
     * @param int $word_len
     * @param int $wild_count
     * @param array $words_arr
     * @return int
     */
    private function nullifyUnmatchedWordsWild(int $word_len, int $wild_count, array &$words_arr): int
    {
        $num_found = count($words_arr);
        foreach ($words_arr as $i => $word) {
            $letter_counts_arr = $this->letter_counts_arr; //copy of the array
            $local_wild_count = $wild_count;
            for ($j = 0; $j < $word_len; $j++) {
                $this->total_compares++;
                $idx = ord($word[$j]) - self::ASCII_a;

                if ($letter_counts_arr[$idx] == 0) {
                    if ($local_wild_count == 0) {
                        $words_arr[$i] = NULL;
                        $num_found--;
                        break;
                    } else {
                        $local_wild_count -= 1;
                    }
                } else {
                    $letter_counts_arr[$idx] -= 1;
                }
            }
        }
        return $num_found;
    }

    /**
     * getWordsArr
     *
     * @param int $word_len
     * @return array
     */
    private function getWordsArr(int $word_len): array
    {
        $key = 'wl_' . $word_len;
        if (Cache::has($key)) {
            $this->cache_count += 1;
            return Cache::get($key);
        }

        $filename = $this->pubpath . "/wordlists/wl_" . $word_len . '.txt';
        $wordlist = explode("*", file_get_contents($filename));
        Cache::forever($key, $wordlist);
        return $wordlist;
    }

    /**
     * get info
     *
     * @param  Request $request
     * @return mixed
     */
    public function find(Request $request)
    {
        $this->time_start = microtime(true);

        $messages = [
            'rt.in' => 'Please enter html or json for the return type (rt) parameter',
            'wc.integer' => 'Please enter a positive integer for the wild count (wc) parameter',
        ];

        $rules = [
            'tray' => 'required|alpha|min:2',
            // 'sm' => 'required|integer|min:0|max:11', //search mode
            'rt' => Rule::in(['html', 'json']), //return type
            'wc' => 'integer|min:0' //wild count
        ];

        $this->validate($request, $rules, $messages);

        $tray = $request->input('tray');
        for ($i = 0, $j = strlen($tray); $i < $j; $i++) {
            $this->letter_counts_arr[ord($tray[$i]) - self::ASCII_a] += 1;
        }

        $wild_count = intval($request->input('wc'));
        $return_type = $request->input('rt');
        $ana_key_len = strlen($tray) + $wild_count;
        if ($ana_key_len > self::NUM_WORDLIST_FILES)
            $ana_key_len = self::NUM_WORDLIST_FILES;

        $json_arr = array();
        $res = '';

        for ($word_len = $ana_key_len; $word_len > 1; $word_len--) {

            $words_arr = $this->getWordsArr($word_len);

            if ($wild_count == 0)
                $num_found = $this->nullifyUnmatchedWords($word_len, $words_arr);
            else if ($word_len > $wild_count)
                $num_found = $this->nullifyUnmatchedWordsWild($word_len, $wild_count, $words_arr);
            else
                $num_found = count($words_arr);

            if ($return_type == self::RETURN_TYPE_JSON) {
                if ($num_found == count($words_arr))
                    $json_arr[] = $words_arr;
                else if ($num_found > 0) {
//                    $json_arr[] = array_filter($words_arr);
                    $temp = array();
                    foreach ($words_arr as $word) {
                        if ($word)
                            $temp[] = $word;
                    }
                    $json_arr[] = $temp;
                }
            } else if ($num_found > 0) {
                $res .= $this->getHTML($word_len, $words_arr);
                $this->total_found += $num_found;
            }
            unset($words_arr);
        }

        if ($return_type == self::RETURN_TYPE_JSON)
            return response()->json($json_arr)
                ->withHeaders([
                    'Access-Control-Allow-Origin' => '*',
                ]);

        $result = '<h5 id="resultsFor">Results for <span>';
        $result .= $tray;
        $result .= '</span></h5>';

        return response($result . $res . $this->getFooter(), 200)
            ->header('Content-Type', 'text/html')
            ->header('Access-Control-Allow-Origin', '*');
    }

    // This function grabs the definition of a word in XML format.
    function define($word)
    {
        $ref = env('DICT_REFERENCES');
        $key = env('DICT_KEY');
        $uri = "https://www.dictionaryapi.com/api/v1/references/" . urlencode($ref) . "/xml/" .
            urlencode($word) . "?key=" . urlencode($key);
        $xml = file_get_contents($uri);
        $xml = simplexml_load_string($xml);
        $xml = json_decode(json_encode((array)$xml), 1);

//        return response()->json($xml)
//            ->withHeaders([
//                'Access-Control-Allow-Origin' => '*',
//            ]);

        $res = '';
        if (!(array_key_exists('entry', $xml))) {
            $res = "No definition found.";
        } else {
            if (!array_key_exists('0', $xml['entry'])) {
                if (array_key_exists('def', $xml['entry'])) {
                    $defs = $xml['entry']['def']['dt'];
                    if (is_array($defs)) {
                        $i = 0;
                        foreach ($defs as $def) {
                            $tmp = trim(str_replace(":", "", $def));
                            if ($tmp)
                                $res .= $tmp . '; ';
                            $i++;
                            if ($i > 2)
                                break;
                        }
                    } else {
                        $tmp = trim(str_replace(":", "", $defs));
                        if ($tmp)
                            $res .= $tmp . '; ';
                    }
                    $res = substr($res, 0, -2);
                } else
                    $res = "No definition found.";
            } else {
                $j = 0;
                foreach ($xml['entry'] as $entry) {
                    if (is_array($entry) && array_key_exists('def', $entry)) {
                        $defs = $entry['def']['dt'];
                        if (is_array($defs)) {
                            $i = 0;
                            foreach ($defs as $def) {
                                $tmp = trim(str_replace(":", "", $def));
                                if ($tmp)
                                    $res .= $tmp . '; ';
                                $i++;
                                if ($i > 2)
                                    break;
                            }
                        } else {
                            $tmp = trim(str_replace(":", "", $defs));
                            if ($tmp)
                                $res .= $tmp . '; ';
                        }
                    }
                    $j++;
                    if ($j > 2)
                        break;
                }
                $res = substr($res, 0, -2);
            }
        }

        return (new Response(ucfirst($res), 200))
            ->header('Access-Control-Allow-Origin', '*');
    }
}
