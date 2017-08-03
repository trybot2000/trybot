<?php
namespace App\Http\Controllers;

use App\Http\Controllers\ApiReturn;
use Intervention\Image\ImageManager;

class ClassHelper
{
    public $root;
    public function __construct()
    {
        $this->root = $_SERVER['DOCUMENT_ROOT'] . "/api/other/ff/";
    }

    protected function initReturn($value = "")
    {
        return new ApiReturn();
    }

    public static function timestampString($format = 'Y-m-d H:i:s', $timeZone = 'America/Chicago')
    {
        return (new \DateTime(null, new \DateTimeZone($timeZone)))->format($format);
    }

    public function formatBytes($size, $precision = 2)
    {
        $base     = log($size, 1024);
        $suffixes = array(
            '',
            'k',
            'M',
            'G',
            'T',
        );

        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    }

    public function _toInt($str)
    {
        return (int) preg_replace("/([^0-9\\.])/i", "", $str);
    }

    public function rand($min = 111111111111, $max = 999999999999)
    {
        return rand($min, $max);
    }

    public function jsonToCsv($json, $csvFilePath = false, $boolOutputFile = false)
    {
        $return = "";

// See if the string contains something
        if (empty($json)) {
            die("The JSON string is empty!");
        }

// If passed a string, turn it into an array
        if (is_array($json) === false) {
            $json = json_decode($json, true);
        }

// If a path is included, open that file for handling. Otherwise, use a temp file (for echoing CSV string)
        if ($csvFilePath !== false) {
            $f = fopen($csvFilePath, 'w+');
            if ($f === false) {
                die("Couldn't create the file to store the CSV, or the path is invalid. Make sure you're including the full path, INCLUDING the name of the output file (e.g. '../save/path/csvOutput.csv')");
            }
        } else {
            $boolEchoCsv = true;
            if ($boolOutputFile === true) {
                $boolEchoCsv = false;
            }

            $strTempFile = 'csvOutput' . date("U") . ".csv";
            $f           = fopen($strTempFile, "w+");
        }

        $firstLineKeys = false;
        foreach ($json as $line) {
            if (empty($firstLineKeys)) {
                $firstLineKeys = array_keys($line);
                fputcsv($f, $firstLineKeys);
                $firstLineKeys = array_flip($firstLineKeys);
            }

            // Using array_merge is important to maintain the order of keys according to the first element
            fputcsv($f, array_merge($firstLineKeys, $line));
        }

        fclose($f);

// Take the file and put it to a string/file for output (if no save path was included in function arguments)
        if ($boolOutputFile === true) {
            if ($csvFilePath !== false) {
                $file = $csvFilePath;
            } else {
                $file = $strTempFile;
            }

// Output the file to the browser (for open/save)
            if (file_exists($file)) {
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename=' . basename($file));
                header('Content-Length: ' . filesize($file));
                readfile($file);
            }
        } elseif ($boolEchoCsv === true) {
            if (($handle = fopen($strTempFile, "r")) !== false) {
                while (($data = fgetcsv($handle)) !== false) {
                    $return .= '"' . implode('","', $data) . '"' . "<br />";
                }

                fclose($handle);
            }
        }

        // Delete the temp file
        unlink($strTempFile);

        return $return;
    }

    public function arrayToTable($arrData, $boolHasHeader = false, $strTableId = "")
    {
        $intRows  = 0;
        $strTable = "<table id='$strTableId'>";

        foreach ($arrData as $k => $v) {
            $strRowType = "td";

            if ($intRows === 0 && $boolHasHeader === true) {
                $strRowType = "th";
                $strTable .= "<thead>";
            }

            $strTable .= "<tr><$strRowType>";
            $strTable .= implode("</$strRowType><$strRowType>", $v);
            $strTable .= "</$strRowType></tr>";

            if ($intRows === 0 && $boolHasHeader === true) {
                $strTable .= "</thead>";
            }

            $intRows += 1;
        }

        $strTable .= "</table>";
        return $strTable;
    }

    public function arrayImplodeNice($array, $ending = 'and')
    {
        $return = "";

        if (!is_array($array)) {
            return $return;
        }

        $countOriginal = count($array);

        if ($countOriginal == 1) {
            return array_shift($array);
        }

        if ($countOriginal == 2) {
            return array_shift($array) . $ending . " " . array_shift($array);
        }

        for ($i = 0; $i < $countOriginal; $i++) {
            if (count($array) >= 2) {
                $return .= array_shift($array) . ", ";
            } else
            if (count($array) == 1) {
                $return .= $ending . " " . array_shift($array);
            }
        }

        return $return;
    }

    public function levenshteinWithArray($string, $array, $boolReturnValue = true)
    {
        $intMin = 999;

        if (!is_string($string) || !is_array($array)) {
            return false;
        }

        foreach ($array as $k => $v) {
            $lev = levenshtein($string, $k);

            if ($lev < $intMin) {
                $intMin    = $lev;
                $bestKey   = $k;
                $bestValue = $v;
            }

            if ($lev == 0) {
                break;
            }
        }

        if ($boolReturnValue === true) {
            return $bestValue;
        }

        return $bestKey;
    }

    public function getTextWidth($text, $font, $fontSize)
    {
        /* Create a new Imagick object */
        $im = new \Imagick();

        /* Create an ImagickDraw object */
        $draw = new \ImagickDraw();

        /* Set the font */
        $draw->setFont($font);
        $draw->setFontSize($fontSize);

        /* Dump the font metrics, autodetect multiline */
        $info = $im->queryFontMetrics($draw, $text);
        return $info['textWidth'];
    }

    public function combineImages($images, $filename, $columns = 2, $boolShowImage = false, $debug = false)
    {
        if (!is_array($images)) {
            return false;
        }

        if (count($images) == 0) {
            return null;
        }

        $manager = new ImageManager(array(
            'driver' => 'imagick',
        ));

        if (count($images) == 1) {
            $manager->make($images[0])->save($this->root . $filename);
            return $filename;
        }

        $wImg      = $manager->make($images[0])->width();
        $w         = ($wImg) * $columns;
        $hImg      = $manager->make($images[0])->height();
        $h         = ceil(count($images) / $columns) * ($hImg);
        $tmpHeight = 0;

        if (count($images) % $columns != 0) {
            $blank         = $manager->canvas($wImg, $hImg);
            $blankFileName = '/api/other/ff/i/blank' . $wImg . $hImg . '.png';
            $blank->save($_SERVER['DOCUMENT_ROOT'] . $blankFileName);
            $images[] = "https://www.reddittryhard.com" . $blankFileName;
        }

        for ($i = 0; $i < count($images); $i++) {
            if (($i % $columns == 0) && ($i > 0)) {
                $tmpHeight += $hImg;
            }

            $stitches[] = ["x" => ($i % $columns == 0 ? 0 : $wImg), "y" => $tmpHeight];
        }

        if ($debug) {
            echo json_encode($stitches);
            exit();
        }

        $background = $manager->canvas($w, $h);

        foreach ($images as $k => $v) {
            $background
                ->insert($manager->make($v), "top-left", $stitches[$k]['x'], $stitches[$k]['y']);
            $background->rectangle($stitches[$k]['x'], $stitches[$k]['y'], $stitches[$k]['x'] + $wImg, $stitches[$k]['y'] + $hImg, function ($draw) {
                $draw->background('rgba(255, 255, 255, 0)');
                $draw->border(2, '#fff');
            }
            );
        }

        $background->rectangle($stitches[0]['x'], $stitches[0]['y'], $stitches[count($images) - 1]['x'] + ($wImg - 1), $stitches[count($images) - 1]['y'] + $hImg - 1, function ($draw) {
            $draw->background('rgba(255, 255, 255, 0)');
            $draw->border(4, '#fff');
        }
        );
        try
        {
            if (file_exists($this->root . $filename)) {
                unlink($this->root . $filename);
            }
        } catch (\Exception $e) {
            die("Can't unlink! <br />" . $e);
        }

        $background->save($this->root . $filename, 100);

        if ($boolShowImage) {
            header('Content-Type:image/png');
            readfile($filename);
        }

        return $filename;
    }

    public function unichr($i)
    {
        return iconv('UCS-4LE', 'UTF-8', pack('V', $i));
    }

    public function isoDateTimeToMySqlFormat($isoDateTime)
    {
        if (!is_string($isoDateTime)) {
            return $isoDateTime;
        }
        $d = \DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $isoDateTime);
        return $d->format('Y-m-d H:i:s');
    }

    public function hash($string, $type = 'md5')
    {
        $s = strtolower(trim(preg_replace('/[^A-Za-z0-9]/i', "", $string)));
        return $type($string);
    }
}
