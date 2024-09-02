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

    /**
     * @return array<string>
     */
    public static function all(): array
    {
        return collect([
            Server::ADAMANTOISE->value,
            Server::CACTUAR->value,
            Server::FAERIE->value,
            Server::GILGAMESH->value,
            Server::JENOVA->value,
            Server::MIDGARDSORMR->value,
            Server::SARGATANAS->value,
            Server::SIREN->value,
            Server::CERBERUS->value,
            Server::LOUISOIX->value,
            Server::MOOGLE->value,
            Server::OMEGA->value,
            Server::RAGNAROK->value,
            Server::SPRIGGAN->value,
            Server::BALMUNG->value,
            Server::BRYNHILDR->value,
            Server::COEURL->value,
            Server::DIABOLOS->value,
            Server::GOBLIN->value,
            Server::MALBORO->value,
            Server::MATEUS->value,
            Server::ZALERA->value,
            Server::AEGIS->value,
            Server::ATOMOS->value,
            Server::CARBUNCLE->value,
            Server::GARUDA->value,
            Server::GUNGNIR->value,
            Server::KUJATA->value,
            Server::RAMUH->value,
            Server::TONBERRY->value,
            Server::TYPHON->value,
            Server::UNICORN->value,
            Server::ALEXANDER->value,
            Server::BAHAMUT->value,
            Server::DURANDAL->value,
            Server::FENRIR->value,
            Server::IFRIT->value,
            Server::RIDILL->value,
            Server::TIAMAT->value,
            Server::ULTIMA->value,
            Server::VALEFOR->value,
            Server::YOJIMBO->value,
            Server::ZEROMUS->value,
            Server::LICH->value,
            Server::ODIN->value,
            Server::PHOENIX->value,
            Server::SHIVA->value,
            Server::ZODIARK->value,
            Server::ANIMA->value,
            Server::ASURA->value,
            Server::BELIAS->value,
            Server::CHOCOBO->value,
            Server::HADES->value,
            Server::IXION->value,
            Server::MANDRAGORA->value,
            Server::MASAMUNE->value,
            Server::PANDAEMONIUM->value,
            Server::SHINRYU->value,
            Server::TITAN->value,
            Server::BEHEMOTH->value,
            Server::EXCALIBUR->value,
            Server::EXODUS->value,
            Server::FAMFRIT->value,
            Server::HYPERION->value,
            Server::LAMIA->value,
            Server::LEVIATHAN->value,
            Server::ULTROS->value,
        ])->sort()->all();
    }

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
