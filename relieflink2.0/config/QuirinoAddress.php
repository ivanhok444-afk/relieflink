<?php

class QuirinoAddress {
    private static $data;

    private static function getData() {
        if (self::$data === null) {
            $json = file_get_contents(__DIR__ . '/../assets/data/quirino-address.json');
            self::$data = json_decode($json, true);
        }

        return self::$data;
    }

    public static function isValid($province, $city, $barangay) {
        $province = trim((string) $province);
        $city = trim((string) $city);
        $barangay = trim((string) $barangay);

        if ($province === '' && $city === '' && $barangay === '') {
            return true;
        }

        if (strcasecmp($province, 'Quirino') !== 0 && strcasecmp($province, 'Quirino Province') !== 0) {
            return false;
        }

        $data = self::getData();
        if (!is_array($data) || !isset($data['municipalities'][$city])) {
            return false;
        }

        return in_array($barangay, $data['municipalities'][$city], true);
    }
}
