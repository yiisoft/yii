<?php
/**
 * Locale data for 'sh'.
 *
 * This file is automatically generated by yiic cldr command.
 *
 * Copyright © 1991-2007 Unicode, Inc. All rights reserved.
 * Distributed under the Terms of Use in http://www.unicode.org/copyright.html.
 *
 * Copyright © 2008-2011 Yii Software LLC (http://www.yiiframework.com/license/)
 */
return array (
  'version' => '4123',
  'numberSymbols' => 
  array (
    'decimal' => '.',
    'group' => ',',
    'list' => ';',
    'percentSign' => '%',
    'plusSign' => '+',
    'minusSign' => '-',
    'exponential' => 'E',
    'perMille' => '‰',
    'infinity' => '∞',
    'nan' => 'NaN',
    'alias' => '',
  ),
  'decimalFormat' => '#,##0.###',
  'scientificFormat' => '#E0',
  'percentFormat' => '#,##0%',
  'currencyFormat' => '¤ #,##0.00',
  'currencySymbols' => 
  array (
    'AUD' => 'AU$',
    'BRL' => 'BR$',
    'CAD' => 'CA$',
    'CNY' => 'CN¥',
    'EUR' => '€',
    'GBP' => '£',
    'HKD' => 'HK$',
    'ILS' => '₪',
    'INR' => '₹',
    'JPY' => 'JP¥',
    'KRW' => '₩',
    'MXN' => 'MX$',
    'NZD' => 'NZ$',
    'THB' => '฿',
    'TWD' => 'NT$',
    'USD' => 'US$',
    'VND' => '₫',
    'XAF' => 'FCFA',
    'XCD' => 'EC$',
    'XOF' => 'CFA',
    'XPF' => 'CFPF',
  ),
  'monthNames' => 
  array (
    'wide' => 
    array (
      1 => '1',
      2 => '2',
      3 => '3',
      4 => '4',
      5 => '5',
      6 => '6',
      7 => '7',
      8 => '8',
      9 => '9',
      10 => '10',
      11 => '11',
      12 => '12',
    ),
    'abbreviated' => 
    array (
      1 => '1',
      2 => '2',
      3 => '3',
      4 => '4',
      5 => '5',
      6 => '6',
      7 => '7',
      8 => '8',
      9 => '9',
      10 => '10',
      11 => '11',
      12 => '12',
    ),
  ),
  'monthNamesSA' => 
  array (
    'narrow' => 
    array (
      1 => '1',
      2 => '2',
      3 => '3',
      4 => '4',
      5 => '5',
      6 => '6',
      7 => '7',
      8 => '8',
      9 => '9',
      10 => '10',
      11 => '11',
      12 => '12',
    ),
  ),
  'weekDayNames' => 
  array (
    'wide' => 
    array (
      0 => '1',
      1 => '2',
      2 => '3',
      3 => '4',
      4 => '5',
      5 => '6',
      6 => '7',
    ),
    'abbreviated' => 
    array (
      0 => '1',
      1 => '2',
      2 => '3',
      3 => '4',
      4 => '5',
      5 => '6',
      6 => '7',
    ),
  ),
  'weekDayNamesSA' => 
  array (
    'narrow' => 
    array (
      0 => '1',
      1 => '2',
      2 => '3',
      3 => '4',
      4 => '5',
      5 => '6',
      6 => '7',
    ),
  ),
  'eraNames' => 
  array (
    'abbreviated' => 
    array (
      0 => 'BCE',
      1 => 'CE',
    ),
    'wide' => 
    array (
      0 => 'BCE',
      1 => 'CE',
    ),
    'narrow' => 
    array (
      0 => 'BCE',
      1 => 'CE',
    ),
  ),
  'dateFormats' => 
  array (
    'full' => 'EEEE, y MMMM dd',
    'long' => 'y MMMM d',
    'medium' => 'y MMM d',
    'short' => 'yyyy-MM-dd',
  ),
  'timeFormats' => 
  array (
    'full' => 'HH:mm:ss zzzz',
    'long' => 'HH:mm:ss z',
    'medium' => 'HH:mm:ss',
    'short' => 'HH:mm',
  ),
  'dateTimeFormat' => '{1} {0}',
  'amName' => 'AM',
  'pmName' => 'PM',
  'orientation' => 'ltr',
  'pluralRules' => 
  array (
    0 => 'fmod(n,10)==1&&fmod(n,100)!=11',
    1 => '(fmod(n,10)>=2&&fmod(n,10)<=4&&fmod(fmod(n,10),1)==0)&&(fmod(n,100)<12||fmod(n,100)>14)',
    2 => 'fmod(n,10)==0||(fmod(n,10)>=5&&fmod(n,10)<=9&&fmod(fmod(n,10),1)==0)||(fmod(n,100)>=11&&fmod(n,100)<=14&&fmod(fmod(n,100),1)==0)',
    3 => 'true',
  ),
);
