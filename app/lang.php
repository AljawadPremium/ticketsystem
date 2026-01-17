<?php
$lang = $_GET['lang'] ?? 'en';

$text = [

    /* ===================== ENGLISH ===================== */
    'en' => [
        'title'   => 'IT Support Ticket',
        'name'    => 'Name',
        'phone'   => 'Phone Number',
        'location'=> 'Location',
        'problem' => 'What is the problem?',
        'submit'  => 'Submit Ticket',
        'other'   => 'Other',

        /* ---------- Locations ---------- */
        'locations' => [
            'Qassim HQ'              => 'Qassim HQ',
            'Riyadh HQ'              => 'Riyadh HQ',
            'Buraidah - Basatin'     => 'Buraidah - Basatin',
            'Buraidah - Nahdha'      => 'Buraidah - Nahdha',
            'Qassim University'      => 'Qassim University',
            'Unaizah'                => 'Unaizah',
            'Arras'                  => 'Arras',
            'Riyadh'                 => 'Riyadh',
            'Hail - Negrah'          => 'Hail - Negrah',
            'Hail - Gishlah'         => 'Hail - Gishlah',
            'Hafar Al Batin'         => 'Hafar Al Batin',
            'Al Duwadimi'            => 'Al Duwadimi',
            'Tabuk'                  => 'Tabuk',
            'Jeddah'                 => 'Jeddah',
            'Dammam'                 => 'Dammam',
            'Al Ahsa'                => 'Al Ahsa',
        ],

        /* ---------- Problem Types ---------- */
        'problems' => [
            'Computer not working'   => 'Computer not working',
            'Windows problem'        => 'Windows problem',
            'Microsoft Office'       => 'Microsoft Office',
            'POS application'        => 'POS application',
            'Network problem'        => 'Network problem',
            'ERPNext improvement'    => 'ERPNext improvement',
            'Other'                  => 'Other',
        ],
    ],

    /* ===================== ARABIC ===================== */
    'ar' => [
        'title'   => 'طلب دعم تقنية المعلومات',
        'name'    => 'الاسم',
        'phone'   => 'رقم الجوال',
        'location'=> 'الموقع',
        'problem' => 'ما هي المشكلة؟',
        'submit'  => 'إرسال الطلب',
        'other'   => 'أخرى',

        /* ---------- Locations ---------- */
        'locations' => [
            'Qassim HQ'              => 'المقر الرئيسي – القصيم',
            'Riyadh HQ'              => 'المقر الرئيسي – الرياض',
            'Buraidah - Basatin'     => 'بريدة – البساتين',
            'Buraidah - Nahdha'      => 'بريدة – النهضة',
            'Qassim University'      => 'جامعة القصيم',
            'Unaizah'                => 'عنيزة',
            'Arras'                  => 'الرس',
            'Riyadh'                 => 'الرياض',
            'Hail - Negrah'          => 'حائل – النقره',
            'Hail - Gishlah'         => 'حائل – القشلة',
            'Hafar Al Batin'         => 'حفر الباطن',
            'Al Duwadimi'            => 'الدوادمي',
            'Tabuk'                  => 'تبوك',
            'Jeddah'                 => 'جدة',
            'Dammam'                 => 'الدمام',
            'Al Ahsa'                => 'الأحساء',
        ],

        /* ---------- Problem Types ---------- */
        'problems' => [
            'Computer not working'   => 'الكمبيوتر لا يعمل',
            'Windows problem'        => 'مشكلة في نظام ويندوز',
            'Microsoft Office'       => 'مشكلة في برامج أوفيس',
            'POS application'        => 'مشكلة في نظام نقاط البيع',
            'Network problem'        => 'مشكلة في الشبكة',
            'ERPNext improvement'    => 'طلب تطوير ERPNext',
            'Other'                  => 'اخرى',
        ],
    ],
];

$t = $text[$lang];
