<?php

namespace Winged\Date;

use \Exception;

/**
 * Example:
 * <code>
 * $date = Date('31/12/2016');
 * $date = Date('12/31/2016');
 * $date = Date('2016/12/31');
 * $date = Date('2016-12-31');
 * $date = Date('31/12/2016 12:32:12');
 * $date = Date('12/31/2016 12:32:12');
 * $date = Date('2016/12/31 12:32:12');
 * $date = Date('2016-12-31 12:32:12');
 * $date = Date('2016-12-31 12:32:12');
 * $date = Date(1483183932);
 * $date = Date(time());
 * $date = Date(strtotime($date));
 * </code>
 *
 * @param string | bool $date
 * @param bool          $hours
 *
 * @return Date
 */
class Date
{
    public $entry = false;
    private $hours = true;
    private $format = '%H:%M:%S';
    private $gregorian;
    private $infos = false;

    /**
     * get time() as Date object
     *
     * @param bool $hours
     *
     * @return Date
     */
    public static function now($hours = true)
    {
        return new Date(time(), $hours);
    }

    /**
     * check if string have a valid date string format
     * aaaa/mm/dd, dd/mm/aaaa or mm/dd/aaaa
     *
     * @param string $date
     *
     * @return bool
     */
    public static function valid($date = '')
    {
        if(!is_scalar($date)){
            return $date;
        }
        $date = trim($date);
        $exp = explode(' ', $date);
        if (count7($exp) == 2) {
            $f1 = explode('/', str_replace('-', '/', $exp[0]));
            if (count7($f1) == 3) {
                if ((strlen($f1[0]) == 2 || strlen($f1[0]) == 4) && (strlen($f1[1]) == 2) && (strlen($f1[2]) == 2 || strlen($f1[2]) == 4)) {
                    $f2 = explode(':', $exp[1]);
                    if (count7($f2) == 3) {
                        if (strlen($f2[0]) == 2 && strlen($f2[1]) == 2 && strlen($f2[2]) == 2) {
                            return true;
                        }
                    }
                }
            }
            return false;
        } else {
            $f1 = explode('/', $exp[0]);
            if (count7($f1) == 3) {
                if ((strlen($f1[0]) == 2 || strlen($f1[0]) == 4) && (strlen($f1[1]) == 2) && (strlen($f1[2]) == 2 || strlen($f1[2]) == 4)) {
                    return true;
                }
            }
            return false;
        }
    }

    /**
     * Date constructor.
     *
     * @param bool $date
     * @param bool $hours
     *
     */
    public function __construct($date = false, $hours = true)
    {
        try{
            $this->rebuild($date, $hours);
        }catch (\Exception $exception){
            trigger_error($exception->getMessage(), E_USER_ERROR);
        }   
        return $this;
    }

    /**
     * @param bool $date
     * @param bool $hours
     *
     * @return $this
     * @throws Exception
     */
    public function rebuild($date = false, $hours = true)
    {
        $infos = $this->normalize($date, $hours);
        $this->entry = $infos['date'];
        $this->hours = $infos['hours'];
        $this->format = $infos['format'];
        $this->gregorian = $infos['gregorian'];
        $infos['timestamp'] = strtotime($infos['date']);
        $this->infos = $infos;
        return $this;
    }

    /**
     * get infos from Date object
     *
     * @return bool | array
     */
    public function getInfo()
    {
        return $this->infos;
    }

    /**
     * check if Date object have gregorian date format
     *
     * @param $exp
     *
     * @return bool
     */
    private function isGregorian($exp)
    {
        if ((int)$exp[1] > 12 && (int)$exp[0] <= 12) {
            return true;
        }
        return false;
    }

    /**
     * return Date object as string with aaaa/mm/dd format
     *
     * @param bool $hours
     *
     * @return string
     */
    public function ymd($hours = true)
    {
        $format = '%Y/%m/%d';
        if ($this->hours && $hours) {
            $format .= ' ' . $this->format;
        }
        return strftime($format, strtotime($this->entry));
    }

    /**
     * return Date object as string with dd/mm/aaaa format
     *
     * @param bool $hours
     *
     * @return string
     */
    public function dmy($hours = true)
    {
        $format = '%d/%m/%Y';
        if ($this->hours && $hours) {
            $format .= ' ' . $this->format;
        }
        return strftime($format, strtotime($this->entry));
    }

    /**
     * return Date object as string with mm/dd/aaaa format
     *
     * @param bool $hours
     *
     * @return string
     */
    public function mdy($hours = true)
    {
        $format = '%m/%d/%Y';
        if ($this->hours && $hours) {
            $format .= ' ' . $this->format;
        }
        return strftime($format, strtotime($this->entry));
    }

    /**
     * return Date object as string with aaaa-mm-dd format
     *
     * @param bool $hours
     *
     * @return string
     */
    public function sql($hours = true)
    {
        $format = '%Y-%m-%d';
        if ($this->hours && $hours) {
            $format .= ' ' . $this->format;
        }
        return strftime($format, strtotime($this->entry));
    }

    /**
     * get timestamp as int from Date object
     *
     * @return int
     */
    public function timestamp()
    {
        return strtotime($this->entry);
    }

    /**
     * pass format like strftime
     *
     * @param string $format
     * @param bool   $capitalize
     * @param array  $not
     *
     * @return bool|string
     */
    public function custom($format = '', $capitalize = true, $not = [])
    {
        try {
            if ($capitalize) {
                if (is_array($not) && count7($not) > 0) {
                    $exp = explode(' ', strftime($format, $this->timestamp()));
                    foreach ($exp as $key => $value) {
                        if (!in_array($value, $not)) {
                            $exp[$key] = ucwords($exp[$key]);
                        }
                    }
                    return implode(' ', $exp);
                }
                return ucwords(strftime($format, $this->timestamp()));
            }
            return strftime($format, $this->timestamp());
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * add time inside date object Ex: ['y' => 0, 'm' => 0, 'w' => 0, 'd' => 0, 'h' => 0, 'i' => 0, 's' => 0]
     *
     * @param array $time
     *
     * @return $this
     * @throws Exception
     */
    public function add($time = ['y' => 0, 'm' => 0, 'w' => 0, 'd' => 0, 'h' => 0, 'i' => 0, 's' => 0])
    {
        $names = [
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        ];

        foreach ($time as $type => $add) {
            $sub = false;
            if (is_int($add)) {
                if ($add < 0) {
                    $sub = true;
                    $add = -$add;
                }
                if ($add > 0) {
                    if ($sub) {
                        $this->rebuild(strtotime('-' . $add . ' ' . $names[$type], $this->timestamp()), $this->hours);
                    } else {
                        $this->rebuild(strtotime('+' . $add . ' ' . $names[$type], $this->timestamp()), $this->hours);
                    }
                }
            }
        }
        return $this;
    }

    /**
     * get diff between two dates, you can choose the diff with ['y', 'm', 'w', 'd', 'h', 'i', 's']
     *
     * @param bool  $date
     * @param array $diffs
     *
     * @return array|bool|int|object|string|null
     * @throws Exception
     */
    public function diff($date = false, $diffs = ['y', 'm', 'w', 'd', 'h', 'i', 's'])
    {
        $infos = $this->normalize($date, $this->hours);
        $start = (int)strtotime($this->entry);
        $end = (int)strtotime($infos['date']);
        $invert = false;
        if ($end > $start) {
            $time = $end - $start;
            $invert = true;
        } else {
            $time = $start - $end;
        }

        $tt = $time;

        $valids = [
            'y' => ['name' => 'years', 'pos' => 0],
            'm' => ['name' => 'months', 'pos' => 1],
            'w' => ['name' => 'weeks', 'pos' => 2],
            'd' => ['name' => 'days', 'pos' => 3],
            'h' => ['name' => 'hours', 'pos' => 4],
            'i' => ['name' => 'minutes', 'pos' => 5],
            's' => ['name' => 'seconds', 'pos' => 6],
        ];

        $differences = [];

        $sorted = [];

        foreach ($diffs as $diff) {
            if (array_key_exists(strtolower($diff), $valids)) {
                $sorted[strtolower($diff)] = $valids[strtolower($diff)]['pos'];
            }
        }

        asort($sorted);
        $sorted = array_flip($sorted);

        foreach ($sorted as $type) {
            if ($type == 'y') {
                $differences[$valids[$type]['name']] = (int)floor($time / (365 * 60 * 60 * 24 + 21600));
                $time = $time % (365 * 60 * 60 * 24);
            }

            if ($type == 'm') {
                $differences[$valids[$type]['name']] = (int)floor($time / 2629800);
                $time = $time % (30 * 24 * 60 * 60);
            }

            if ($type == 'w') {
                $differences[$valids[$type]['name']] = (int)floor($time / (7 * 24 * 60 * 60));
                $time = $time % (7 * 24 * 60 * 60);
            }

            if ($type == 'd') {
                $differences[$valids[$type]['name']] = (int)floor($time / (24 * 60 * 60));
                $time = $time % (24 * 60 * 60);
            }

            if ($type == 'h') {
                $differences[$valids[$type]['name']] = (int)floor($time / (60 * 60));
                $time = $time % (60 * 60);
            }

            if ($type == 'i') {
                $differences[$valids[$type]['name']] = (int)floor($time / (60));
                $time = $time % (60);
            }

            if ($type == 's') {
                $differences[$valids[$type]['name']] = $time;
            }
        }

        $differences['extra'] = [];
        $differences['extra']['total_difference_in_seconds'] = $tt;
        $differences['extra']['end_higher_start'] = $invert;
        $differences['extra']['startDate'] = [
            'date' => $this->entry,
            'timestamp' => $start,
            'date_info' => $this->getInfo(),
        ];
        $differences['extra']['endDate'] = [
            'date' => $infos['date'],
            'timestamp' => $end,
            'date_info' => $infos,
        ];

        return $this->recursiveObject($differences);
    }

    /**
     * Return true for bissextile year and false for nom bissextile year
     *
     * @param bool | int | string $year
     *
     * @return bool
     */
    public function bissextile($year = false)
    {
        if (!$year) {
            $year = (int)$this->custom('%Y', false);
        }
        if ((($year % 4) == 0 && ($year % 100) != 0) || (($year % 400) == 0)) {
            return true;
        }
        return false;
    }

    /**
     * Return stdClass with begin date of week in current day of date and end date of week in Saturday
     * If from_first_day $ is true, the first day is always Sunday.
     *
     * @param bool $from_first_day
     *
     * @return \stdClass
     */
    public function weekInterval($from_first_day = false)
    {
        $day = strtolower(date('D'));
        $locale = _getlocale();
        _setlocale();
        $days = [
            'sun' => [
                'first' => 0,
                'last' => 6,
            ],
            'mon' => [
                'first' => 1,
                'last' => 5,
            ],
            'tue' => [
                'first' => 2,
                'last' => 4,
            ],
            'wed' => [
                'first' => 3,
                'last' => 3,
            ],
            'thu' => [
                'first' => 4,
                'last' => 2,
            ],
            'fri' => [
                'first' => 5,
                'last' => 1,
            ],
            'sat' => [
                'first' => 6,
                'last' => 0,
            ]
        ];

        $interval = $days[$day];

        _setlocale($locale);

        if ($from_first_day) {

            return $this->recursiveObject([
                'begin' => date('Y-m-d', $this->timestamp()),
                'end' => date('Y-m-d', $this->timestamp() + $interval['last'] * 24 * 60 * 60)
            ]);

        }

        return $this->recursiveObject([
            'begin' => date('Y-m-d', $this->timestamp() - $interval['first'] * 24 * 60 * 60),
            'end' => date('Y-m-d', $this->timestamp() + $interval['last'] * 24 * 60 * 60)
        ]);
    }

    /**
     * Return stdClass with begin date of month in current day of date and last day of month
     * If from_first_day $ is true, the first day is always 01.
     *
     * @param bool $from_first_day
     *
     * @return \stdClass
     */
    public function monthInterval($from_first_day = true)
    {
        if ($from_first_day) {
            return $this->recursiveObject([
                'begin' => date('Y-m-d', $this->timestamp()),
                'end' => date("Y-m-t", $this->timestamp())
            ]);
        }
        return $this->recursiveObject([
            'begin' => date('Y-m-01', $this->timestamp()),
            'end' => date("Y-m-t", $this->timestamp())
        ]);
    }

    /**
     * Test if current date in object bigger then $date
     *
     * @param $date
     *
     * @return bool
     * @throws Exception
     */
    public function greater($date)
    {
        $infos = $this->normalize($date, $this->hours);
        $start = (int)strtotime($this->entry);
        $end = (int)strtotime($infos['date']);
        return $start > $end;
    }

    /**
     * Test if current date in object less than $date
     *
     * @param $date
     *
     * @return bool
     * @throws Exception
     */
    public function smaller($date)
    {
        $infos = $this->normalize($date, $this->hours);
        $start = (int)strtotime($this->entry);
        $end = (int)strtotime($infos['date']);
        return $start < $end;
    }

    /**
     * @param bool $date
     * @param bool $hours
     *
     * @return array
     * @throws Exception
     */
    private function normalize($date = false, $hours = true)
    {
        if (is_object($date)) {
            if (get_class($date) == 'Winged\Date\Date') {
                /**
                 * @var $date Date
                 */
                $date = $date->dmy(true);
            }
        }
        if (is_int($date)) {
            $date = date('Y/m/d H:i:s', $date);
        }
        $date = trim($date);
        if (!$date) {
            if (!$hours) {
                $date = date('Y/m/d');
            } else {
                $date = date('Y/m/d H:i:s');
            }
        }

        $format = '%H:%M:%S';

        $nd = explode(' ', $date);

        if ($hours && count7($nd) == 1) {
            $hours = false;
        }
        if (count7($nd) == 2) {
            $hs = explode(':', $nd[1]);
            if (count7($hs) > 0) {
                $format = '';
                $format_h = count7($hs);
                $vect = [
                    '%H',
                    '%M',
                    '%S'
                ];
                for ($x = 0; $x < $format_h; $x++) {
                    if ($x == 0) {
                        $format .= $vect[$x];
                    } else {
                        $format .= ':' . $vect[$x];
                    }
                }
            }

        }

        $c = str_replace(['-', '/'], ';', $nd[0]);
        $exp = explode(';', $c);
        if (count7($exp) != 3) {
            throw new Exception('Invalid date string.');
        }

        $gregorian = $this->isGregorian($exp);

        if (!$hours) {
            $ndr = explode(' ', $date);
            $date = array_shift($ndr);
        }

        $c = explode(' ', $c);
        $od = explode(';', $c[0]);

        if (count7($od) == 3) {
            if ($gregorian) {
                $a = $od[0];
                $od[0] = $od[1];
                $od[1] = $a;
                if (strlen($od[2]) === 4) {
                    $a = $od[2];
                    $od[2] = $od[0];
                    $od[0] = $a;
                }
                if ($hours) {
                    $date = implode('/', $od) . ' ' . $nd[1];
                } else {
                    $date = implode('/', $od);
                }
            }
            $c = explode(' ', $date);
            $od = explode('/', str_replace('-', '/', $c[0]));
            if (strlen($od[2]) === 4) {
                $a = $od[2];
                $od[2] = $od[0];
                $od[0] = $a;
                if ($hours) {
                    $date = implode('/', $od) . ' ' . $c[1];
                } else {
                    $date = implode('/', $od);
                }
            }

        }

        return [
            'date' => str_replace('/', '-', $date),
            'hours' => $hours,
            'format' => $format,
            'gregorian' => $gregorian,
        ];
    }

    /**
     * convert array into object recursive
     *
     * @param array $arg
     *
     * @return object | \stdClass | bool | array
     */
    protected function recursiveObject($arg)
    {
        if (is_array($arg)) {
            $arg = (object)$arg;
        } else {
            return $arg;
        }
        foreach ($arg as $key => $value) {
            if (is_array($value)) {
                $value = $this->recursiveObject($value);
                $arg->{$key} = $value;
            } else {
                $arg->{$key} = $value;
            }
        }
        return $arg;
    }

}

/**
 * get current LC_TIME locale
 *
 * @return string
 */
function _getlocale()
{
    return setlocale(LC_TIME, 0);
}

/**
 * set LC_TIME locale
 *
 * @param bool | string $lang_charset
 */
function _setlocale($lang_charset = false)
{
    if (!$lang_charset) {
        $lang_charset = 'english';
    }
    setlocale(LC_TIME, $lang_charset);
    @putenv('LANG=' . $lang_charset);
    @putenv('LANGUAGE=' . $lang_charset);
    $locale = setlocale(LC_TIME, 0);
    setlocale(LC_TIME, 'C');
    setlocale(LC_TIME, $locale);
}