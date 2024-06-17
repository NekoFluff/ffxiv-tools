<?php

namespace App\Models\Enums;

enum DataCenter: string
{
    case AETHER = 'Aether';
    case CHAOS = 'Chaos';
    case CRYSTAL = 'Crystal';
    case ELEMENTAL = 'Elemental';
    case GAIA = 'Gaia';
    case LIGHT = 'Light';
    case MANA = 'Mana';
    case PRIMAL = 'Primal';

    /** @return array<Server> */
    public function servers(): array
    {
        return match ($this) {
            self::AETHER => [
                Server::ADAMANTOISE,
                Server::CACTUAR,
                Server::FAERIE,
                Server::GILGAMESH,
                Server::JENOVA,
                Server::MIDGARDSORMR,
                Server::SARGATANAS,
                Server::SIREN,
            ],
            self::CHAOS => [
                Server::CERBERUS,
                Server::LOUISOIX,
                Server::MOOGLE,
                Server::OMEGA,
                Server::RAGNAROK,
                Server::SPRIGGAN,
            ],
            self::CRYSTAL => [
                Server::BALMUNG,
                Server::BRYNHILDR,
                Server::COEURL,
                Server::DIABOLOS,
                Server::GOBLIN,
                Server::MALBORO,
                Server::MATEUS,
                Server::ZALERA,
            ],
            self::ELEMENTAL => [
                Server::AEGIS,
                Server::ATOMOS,
                Server::CARBUNCLE,
                Server::GARUDA,
                Server::GUNGNIR,
                Server::KUJATA,
                Server::RAMUH,
                Server::TONBERRY,
                Server::TYPHON,
                Server::UNICORN,
            ],
            self::GAIA => [
                Server::ALEXANDER,
                Server::BAHAMUT,
                Server::DURANDAL,
                Server::FENRIR,
                Server::IFRIT,
                Server::RIDILL,
                Server::TIAMAT,
                Server::ULTIMA,
                Server::VALEFOR,
                Server::YOJIMBO,
                Server::ZEROMUS,
            ],
            self::LIGHT => [
                Server::LICH,
                Server::ODIN,
                Server::PHOENIX,
                Server::SHIVA,
                Server::ZODIARK,
            ],
            self::MANA => [
                Server::ANIMA,
                Server::ASURA,
                Server::BELIAS,
                Server::CHOCOBO,
                Server::HADES,
                Server::IXION,
                Server::MANDRAGORA,
                Server::MASAMUNE,
                Server::PANDAEMONIUM,
                Server::SHINRYU,
                Server::TITAN,
            ],
            self::PRIMAL => [
                Server::BEHEMOTH,
                Server::EXCALIBUR,
                Server::EXODUS,
                Server::FAMFRIT,
                Server::HYPERION,
                Server::LAMIA,
                Server::LEVIATHAN,
                Server::ULTROS,
            ],
        };
    }
}
