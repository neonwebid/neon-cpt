# NeonCPT: Custom Post Type Helper: Cara Pakai NeonCPT

Untuk menggunakan NEONCPT ada 2 cara:

1. copy-paste kode class `NEONCPT` kedalam file `functions.php`
1. meng-upload file `class.neon-cpt.php` kedalam folder tema/plugin yang digunakan

> Baca Juga: Panduan [WP Coding Standard](https://github.com/neonwebid/neon-coding-standards/blob/master/basic-coding-standard.md)

## As Menu
membuat cpt sebagai menu selayaknya `Posts` atau `Pages`

```php

class CustomerMenu extends NEONCPT {

	public function __construct() {
		parent::__construct( [
			'id'                 => 'customers',
			'title'              => 'Customers',
			'supports'           => [ 'title', 'thumbnail' ],
			'menu_position'      => 25,
			'menu_icon'          => 'dashicons-id-alt',
			'show_in_rest'       => false,
			'has_archive'        => true,
		        'hierarchical'       => false,
		        'show_in_menu'       => true,
			'publicly_queryable' => true,
			'group'              => [
				[
					'id'                => 'customer-label',
					'title'             => 'Label',
					'show_admin_column' => true,
				],
				[
					'id'                => 'customer-business',
					'title'             => 'Business Category',
					'show_admin_column' => true,
				],
			],
			'columns' => [
				'column_id' => [
					'title' => 'Column Title',
					'value' => 'meta_key',
					'sort_by' => 'meta_key',
					'sort_type' => 'number',
					'before' => 'date'
				]
			],
			'unset_columns' => [

			]
		] );
	}

}

new CustomerMenu();
```

untuk keterangan parameter selengkapnya dapat dilihat di [sini](https://developer.wordpress.org/reference/functions/register_post_type/#parameters)


## As SubMenu

Untuk memasukkan CPT kedalam submenu:  
Ubah value ``show_in_menu`` dari ``true`` menjadi file lokasi.

### File Lokasi
- Settings = `'options-general.php'`
- Tools    = `'tools.php'`
- Themes   = `'themes.php'`
- Pages    = `'edit.php?post_type=page'`
- Posts    = `'edit.php?post_type=post'`
- CPT Lain = `'edit.php?post_type=$idcpt'`. ubah ``$idcpt`` dengan target cpt yang diinginkan

## Only On WP Admin
Untuk membuat CPT yang hanya bisa diakses melalui halaman Admin dan tidak bisa diakses secara public oleh visito dari FrontEnd.

Ubah value ``publicly_queryable`` dari ``true`` ke ``false``

## Set Custom Taxonomy
Untuk membuat taxonomy selayaknya Category & Tags pada posts adalah dengan membuat array **group** :

```php
...

'group' => [
  [
    'id'                => 'customer-label',
    'title'             => 'Label',
    'show_admin_column' => true,
  ],
  [
    'id'                => 'customer-business',
    'title'             => 'Business Category',
    'hierarchical'      => true,
    'show_admin_column' => true,
  ],
]

...

```

Untuk membuat Taxonomy seperti _category pada posts_ yang memiliki tingkatan.  
Ubah atau tambahkan ``hierarchical`` dengan nilai ``true``

## Get Custom Taxonomy
Secara default setelah membuat taxonomy, taxonomy tidak akan langsung muncul dihalaman depan (Front End). Perlu menambahkan function khusus yang diletakan di halaman single.

```php
...

if ( ! function_exists( 'neon_current_tax' ) ) {
	function neon_current_tax( $taxonomy, $before = '', $after = '' ) {
		global $post;
		$taxonomies = [];
		$terms      = get_the_terms( $post->ID, $taxonomy );
		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$taxonomies[] = sprintf('<a href="%s" title="%s">%s</a>',
					get_term_link( $term ),
					$term->name,
					$term->name
				);
			}
		}

		return $before . implode('', $taxonomies) . $after;
	}
}

...
```

**Cara Menggunakan**
pastikan function khusus diletakan di loop atau single

```php
<?php neon_current_tax('customer-label', '<div class="customer-label"><span>Customer Labels</span>', '</div>'); ?>
```

atau

```php
<?php neon_current_tax('customer-label'); ?>
```

## Need Helps
:warning:  Jika ada pertanyaan dapat ditanyakan dimenu [diskusi](https://github.com/neonwebid/neon-cpt/discussions) :coffee:  
:link:  Visit Our Site: [neon.web.id](https://neon.web.id)
