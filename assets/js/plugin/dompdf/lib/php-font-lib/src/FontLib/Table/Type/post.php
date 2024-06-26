<?php


namespace FontLib\Table\Type;
use FontLib\Table\Table;
use FontLib\TrueType\File;


class post extends Table {
  protected $def = array(
    "format"             => self::Fixed,
    "italicAngle"        => self::Fixed,
    "underlinePosition"  => self::FWord,
    "underlineThickness" => self::FWord,
    "isFixedPitch"       => self::uint32,
    "minMemType42"       => self::uint32,
    "maxMemType42"       => self::uint32,
    "minMemType1"        => self::uint32,
    "maxMemType1"        => self::uint32,
  );

  protected function _parse() {
    $font = $this->getFont();
    $data = $font->unpack($this->def);

    $names = array();

    switch ($data["format"]) {
      case 1:
        $names = File::$macCharNames;
        break;

      case 2:
        $data["numberOfGlyphs"] = $font->readUInt16();

        $glyphNameIndex = $font->readUInt16Many($data["numberOfGlyphs"]);

        $data["glyphNameIndex"] = $glyphNameIndex;

        $namesPascal = array();
        for ($i = 0; $i < $data["numberOfGlyphs"]; $i++) {
          $len           = $font->readUInt8();
          $namesPascal[] = $font->read($len);
        }

        foreach ($glyphNameIndex as $g => $index) {
          if ($index < 258) {
            $names[$g] = File::$macCharNames[$index];
          }
          else {
            $names[$g] = $namesPascal[$index - 258];
          }
        }

        break;

      case 2.5:
        
        break;

      case 3:
        
        break;

      case 4:
        
        break;
    }

    $data["names"] = $names;

    $this->data = $data;
  }

  function _encode() {
    $font           = $this->getFont();
    $data           = $this->data;
    $data["format"] = 3;

    $length = $font->pack($this->def, $data);

    return $length;
    
  }
}