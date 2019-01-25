
## Cara Cepat bikin Content Type.

Buat file dengan nama `entity.type.node.bundle.yml` atau dapat disingkat dengan
nama file `content.yml`.

Lalu isi file tersebut dengan string format machine_name sbb:

```
submission:
article:
page:
```

Eksekusi `gpl`, maka otomatis akan terbuat tiga buah content type,
yakni: `submission`, `article`, `page`.

Format machine_name yakni: huruf (a-z) kecil (strtolower) dan
underscore saja yang diperbolehkan.

## Cara Cepat Menambah Property Content Type.

Buat file dengan nama `entity.type.node.bundle.blog.yml` atau dapat disingkat dengan
nama file `content.blog.yml`.

Lalu isi file tersebut dengan string format machine_name sbb:

```
label: My Blog
```

Eksekusi `gpl`, maka otomatis akan berubah label entity node bundle Blog.
