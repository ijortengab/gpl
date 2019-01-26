Petunjuk awal:
 - Seluruh file yang dibuat pada panduan ini berada pada direktori `private://gpl/config`
 - Eksekusi `gpl` merupakan versi singkat dari `DRUPAL_ROOT/vendor/bin/gpl`
 - Format machine_name yakni: huruf (a-z) kecil (strtolower) dan underscore saja yang diperbolehkan.

## Cara Cepat bikin Content Type.

### Cara 1 dari 2

Buat file dengan nama `entity.type.node.bundle.yml` atau dapat disingkat dengan nama file `content.yml`.

Lalu isi file tersebut dengan string format machine_name sbb:

```
article:
page:
```

Eksekusi `gpl`, maka otomatis akan terbuat dua buah content type, yakni: `article`, `page`.

Untuk seterusnya file `content.yml` akan digunakan sebagai versi singkat dari `entity.type.node.bundle.yml`.

### Cara 2 dari 2

Buat file dengan nama `content.blog.yml`. Kemudian eksekusi `gpl`, maka akan otomatis terbuat content type baru yakni: `blog`.

## Cara Cepat Menambah Property Content Type.

### Cara 1 dari 2

Modifikasi file `content.yml` ubah isinya menjadi sebagai berikut:

```
article:
	preview: disabled
page:
	label: Web Page
```
Eksekusi `gpl`, maka otomatis akan berubah konfigurasi dari tipe konten `article` maupun `page`.

Selain property `preview` dan `label`. Juga terdapat berbagai property, yakni:
  - label
  - title_label
  - description
  - preview (disabled,optional,required)
  - guidelines
  - default_options: (Multivalue)
    - published
    - promoted
    - sticky
    - revision
  - display_options: (Multivalue)
    - author_date

###  Cara 2 dari 2

Selain menggunakan file `content.yml`, kita juga bisa spesifik pada content type tertentu misalnya `content.blog.yml` untuk tipe content type `blog`. Contoh isi dari file `content.blog.yml` yakni:

```
label: My Blog
default_options:
  - published
```

Catatan:

Jika content type `blog` sudah didefinisikan pada file  `content.blog.yml`, maka jangan mendefinisikannya juga pada file `content.yml` agar tidak terjadi override yang tidak diharapkan.
