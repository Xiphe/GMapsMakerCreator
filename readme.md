Google Maps Marker Creator
==========================

A small API that creates custom markers for Google Maps

**Source Sans Pro** by [Paul D. Hunt](https://plus.google.com/108888178732927400671/about)
[SIL Open Font License, 1.1](http://scripts.sil.org/OFL)




Options
-------

Thus far, the following options can be passed into maker creation.
Either by setting them inside the $_GET variable or by passing them into the construction.

**content** [default: '•']
	The Letters or Symbols shown on the Marker. (Maximal two letters)

**font-weight** [default: 'normal']
	Can be 'normal' or 'bold'.

**color** [default: '000000']
	The RGB Font-Color in hexadecimal format.

**border-color** [default: '9D3E38']
	The RGB Border-Color in hexadecimal format.

**background-color** [default: 'FE7D72']
	The RGB Background-Color in hexadecimal format.

**font-size** [default: 17]
	The font size for the content.

**left** [default: 6]
	X-Offset of the content

**top** [default: 16]
	Y-Offset of the content




Example
-------

```php
/* include the class file. */
include 'GMapsMarker.php';

/* Display the Marker image */
echo new Xiphe\GMaps\Marker(array('content' => 'A'));
```



Todo
----

+ Implement @2x
+ Other Fonts




License
-------

Copyright (C) 2013 Hannes Diercks

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.