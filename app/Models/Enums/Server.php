<?php

namespace App\Models\Enums;

enum Server: string
{
    case ADAMANTOISE = 'Adamantoise';
    case CACTUAR = 'Cactuar';
    case FAERIE = 'Faerie';
    case GILGAMESH = 'Gilgamesh';
    case JENOVA = 'Jenova';
    case MIDGARDSORMR = 'Midgardsormr';
    case SARGATANAS = 'Sargatanas';
    case SIREN = 'Siren';
    case CERBERUS = 'Cerberus';
    case LOUISOIX = 'Louisoix';
    case MOOGLE = 'Moogle';
    case OMEGA = 'Omega';
    case RAGNAROK = 'Ragnarok';
    case SPRIGGAN = 'Spriggan';
    case BALMUNG = 'Balmung';
    case BRYNHILDR = 'Brynhildr';
    case COEURL = 'Coerul';
    case DIABOLOS = 'Diabolos';
    case GOBLIN = 'Goblin';
    case MALBORO = 'Malboro';
    case MATEUS = 'Mateus';
    case ZALERA = 'Zalera';
    case AEGIS = 'Aegis';
    case ATOMOS = 'Atomos';
    case CARBUNCLE = 'Carbuncle';
    case GARUDA = 'Garuda';
    case GUNGNIR = 'Gungnir';
    case KUJATA = 'Kujata';
    case RAMUH = 'Ramuh';
    case TONBERRY = 'Tonberry';
    case TYPHON = 'Typhon';
    case UNICORN = 'Unicorn';
    case ALEXANDER = 'Alexander';
    case BAHAMUT = 'Bahamut';
    case DURANDAL = 'Durandal';
    case FENRIR = 'Fenrir';
    case IFRIT = 'Ifrit';
    case RIDILL = 'Ridill';
    case TIAMAT = 'Tiamat';
    case ULTIMA = 'Ultima';
    case VALEFOR = 'Valefor';
    case YOJIMBO = 'Yojimbo';
    case ZEROMUS = 'Zeromus';
    case LICH = 'Lich';
    case ODIN = 'Odin';
    case PHOENIX = 'Phoenix';
    case SHIVA = 'Shiva';
    case ZODIARK = 'Zodiark';
    case ANIMA = 'Anima';
    case ASURA = 'Asura';
    case BELIAS = 'Belias';
    case CHOCOBO = 'Chocobo';
    case HADES = 'Hades';
    case IXION = 'Ixion';
    case MANDRAGORA = 'Mandragora';
    case MASAMUNE = 'Masamune';
    case PANDAEMONIUM = 'Pandaemonium';
    case SHINRYU = 'Shinryu';
    case TITAN = 'Titan';
    case BEHEMOTH = 'Behemoth';
    case EXCALIBUR = 'Excalibur';
    case EXODUS = 'Exodus';
    case FAMFRIT = 'Famfrit';
    case HYPERION = 'Hyperion';
    case LAMIA = 'Lamia';
    case LEVIATHAN = 'Leviathan';
    case ULTROS = 'Ultros';

    public function dataCenter(): DataCenter
    {
        return match ($this) {
            self::ADAMANTOISE, self::CACTUAR, self::FAERIE, self::GILGAMESH, self::JENOVA, self::MIDGARDSORMR, self::SARGATANAS, self::SIREN => DataCenter::AETHER,
            self::CERBERUS, self::LOUISOIX, self::MOOGLE, self::OMEGA, self::RAGNAROK, self::SPRIGGAN => DataCenter::CHAOS,
            self::BALMUNG, self::BRYNHILDR, self::COEURL, self::DIABOLOS, self::GOBLIN, self::MALBORO, self::MATEUS, self::ZALERA => DataCenter::CRYSTAL,
            self::AEGIS, self::ATOMOS, self::CARBUNCLE, self::GARUDA, self::GUNGNIR, self::KUJATA, self::RAMUH, self::TONBERRY, self::TYPHON, self::UNICORN => DataCenter::ELEMENTAL,
            self::ALEXANDER, self::BAHAMUT, self::DURANDAL, self::FENRIR, self::IFRIT, self::RIDILL, self::TIAMAT, self::ULTIMA, self::VALEFOR, self::YOJIMBO, self::ZEROMUS => DataCenter::GAIA,
            self::LICH, self::ODIN, self::PHOENIX, self::SHIVA, self::ZODIARK => DataCenter::LIGHT,
            self::ANIMA, self::ASURA, self::BELIAS, self::CHOCOBO, self::HADES, self::IXION, self::MANDRAGORA, self::MASAMUNE, self::PANDAEMONIUM, self::SHINRYU, self::TITAN => DataCenter::MANA,
            self::BEHEMOTH, self::EXCALIBUR, self::EXODUS, self::FAMFRIT, self::HYPERION, self::LAMIA, self::LEVIATHAN, self::ULTROS => DataCenter::PRIMAL,
        };
    }
}
