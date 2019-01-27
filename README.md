## Tentang

GPL adalah:

 - Gak Pake Lama

 - Drupal Generator bekerja seperti layaknya Static Generator. Menggunakan
   file plain text sebagai dasar membangun Drupal.

 - Pelengkap Drupal Tools Command Line Interface lainnya, seperti drush,
   drupal console, dan coder.

 - Ditujukan untuk pengembangan sistem informasi manajemen yang cepat.

Filosofi GPL adalah:

 - Untuk keperluan spesifik, maka akan digunakan autocreate custom module, yang
   memaksimalkan penggunaaan hook drupal.

 - Seminimal mungkin dalam menggunakan contrib module. Sebagai contoh:
   module rules tidak diperlukan. Untuk kebutuhan conditional proses dan event
   maka akan dibuat custom module. Module flag dan workflow tidak diperlukan.
   Untuk kebutuhan memberi tanda atau status, maka akan memaksimalkan penggunaan
   taxonomy term dan menggunakan hook Drupal semaksimal mungkin.

## Inspirasi

Project ini terinspirasi dari Vagrant dan Docker

## Fitur di Drupal 7

Menyediakan tipe element baru yang mendukung untuk Bootstrap Front End Framework.

- '#type' => 'input_group'

## Pengembangan

Project ini belum ditaro di packagist.org,

Tambahkan informasi berikut pada composer.json.

```json
{
    "minimum-stability": "dev",
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/ijortengab/gpl"
        }
    ]
}
```

Tambah dengan cara `composer require ijortengab/gpl`.
