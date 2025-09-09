<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'u542077544_OSRGConnect' );

/** Database username */
define( 'DB_USER', 'u542077544_Omer' );

/** Database password */
define( 'DB_PASSWORD', 'V0Zw7celP]AO9' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'I.s]d4XQ)|fro_.i&%5zK|a.z4}0|4:54U4]9x*P,*O/V$D|^1ucK#G|nH]jb{s4' );
define( 'SECURE_AUTH_KEY',  '8)ovdS6?OQ|?!/0i|vizPoK_+V<;dZ<57wNj6GYKkkw=LWN~*ifZ%$>&:,8b4T8:' );
define( 'LOGGED_IN_KEY',    '`;X,c><PN3H2&pM#9szLz9gt,Hu`VY8G;05<wcHe,O[NwG|}+;,+Kc*c*&/c@kmE' );
define( 'NONCE_KEY',        '=NxC$3MGdAoD[q:kMPgA;[qMH#o~/pd]S3Gy9[;{9El_=)wv{U^OFq;yE2QYP5Rb' );
define( 'AUTH_SALT',        'pDL*:jBj-Cf&jT&];=,-l7E^o;{Kfx!b$y`hj.4W0AQ0WmWy[oph@z*$]mR:w=39' );
define( 'SECURE_AUTH_SALT', 'PdOE3v[00pl(yS^<@?fo&v00x (`XMguR>Svk{xnwE+h$tYfQ,W47VM8)J,p)1V)' );
define( 'LOGGED_IN_SALT',   't6|H>b.2H6~4jICL_GIGVv)k4aye[#%%F$6-^h S.E9G$+Iq!#,1[PBn:]DUQmCt' );
define( 'NONCE_SALT',       '}4!I)GD.sgC{ZlZ1XN?}O*fS,pxV!0aN1/NnsNmss(3]oE-z5AW)H*0u[a?!2r!6' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_LOG', false );
define( 'WP_DEBUG_DISPLAY', false );
define( 'SCRIPT_DEBUG', false );
define( 'DISALLOW_FILE_EDIT', true );
define( 'CLASSIC_EDITOR_REPLACE', true );
ini_set('display_errors', 0);

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
