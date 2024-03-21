Webform XLSX export
===================

INTRODUCTION
------------

This module provides a Webform submission exporter
that can be used to export submissions
in the Office Open XML format used by Microsoft Excel.

Webform itself includes a table exporter that can generate .xls files
but these trigger warnings in recent Microsoft Excel versions
(because they are not real Excel files).
So this module is a replacement that produces valid Excel files.


REQUIREMENTS
------------

 * [PhpSpreadsheet](https://github.com/PHPOffice/PhpSpreadsheet)


INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module. Visit
   <https://www.drupal.org/documentation/install/modules-themes/modules-8>
   for further information.
   Installing with Composer is recommended
   because it will install PhpSpreadsheet automatically as well.


CONFIGURATION
-------------

The module has no menu or modifiable settings.
It will add a new export format to the Webform export page.


MAINTAINERS
-----------

Current maintainers:

 * Pierre Rudloff (prudloff) - <https://www.drupal.org/u/prudloff>

This project has been sponsored by:

 * Insite:
    A lasting and independent business project
    with more than 20 years experience
    and several hundred references in web projects and visual communication
    with public actors, associative and professional networks.
    Visit <https://www.insite.coop/> for more information.
