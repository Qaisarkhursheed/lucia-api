<?php

namespace Database\Seeders;

use App\Models\Airline;
use Illuminate\Database\Seeder;

class AirlinesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ( $this->getData() as $item) {
            Airline::updateOrInsert(['id' => $item['id']], $item);
        }
    }

    /**
     * @return array[]
     */
    private function getData(): array
    {
        return [
            [
                "id" => 1,
                "iata" => "0B",
                "icao" => "BLA",
                "name" => "Blue Air"
            ],
            [
                "id" => 2,
                "iata" => "0C",
                "icao" => "CBR",
                "name" => "Cobra Aviation"
            ],
            [
                "id" => 3,
                "iata" => "0J",
                "icao" => "PJZ",
                "name" => "Premium Jet"
            ],
            [
                "id" => 4,
                "iata" => "0V",
                "icao" => "VFC",
                "name" => "VASCO"
            ],
            [
                "id" => 5,
                "iata" => "1I",
                "icao" => "EJA",
                "name" => "NetJets Aviation"
            ],
            [
                "id" => 6,
                "iata" => "2B",
                "icao" => "AWT",
                "name" => "Albawings"
            ],
            [
                "id" => 7,
                "iata" => "2D",
                "icao" => "EAL",
                "name" => "Eastern Airlines"
            ],
            [
                "id" => 8,
                "iata" => "2I",
                "icao" => "CSB",
                "name" => "21 Air"
            ],
            [
                "id" => 9,
                "iata" => "2J",
                "icao" => "VBW",
                "name" => "Air Burkina"
            ],
            [
                "id" => 10,
                "iata" => "2K",
                "icao" => "GLG",
                "name" => "Avianca Ecuador"
            ],
            [
                "id" => 11,
                "iata" => "2L",
                "icao" => "BOL",
                "name" => "TAB"
            ],
            [
                "id" => 12,
                "iata" => "2P",
                "icao" => "GAP",
                "name" => "PAL Express and Airphil Express"
            ],
            [
                "id" => 13,
                "iata" => "2Y",
                "icao" => "MYU",
                "name" => "My Indo Airlines"
            ],
            [
                "id" => 14,
                "iata" => "3E",
                "icao" => "ACO",
                "name" => "Air Choice One"
            ],
            [
                "id" => 15,
                "iata" => "3G",
                "icao" => "CXM",
                "name" => "World Cargo Airlines"
            ],
            [
                "id" => 16,
                "iata" => "3H",
                "icao" => "AIE",
                "name" => "Air Inuit"
            ],
            [
                "id" => 17,
                "iata" => "3K",
                "icao" => "JSA",
                "name" => "Jetstar Asia"
            ],
            [
                "id" => 18,
                "iata" => "3L",
                "icao" => "ADY",
                "name" => "Air Arabia Abu Dhabi"
            ],
            [
                "id" => 19,
                "iata" => "3M",
                "icao" => "SIL",
                "name" => "Silver Airways"
            ],
            [
                "id" => 20,
                "iata" => "3N",
                "icao" => "URG",
                "name" => "Air Urga"
            ],
            [
                "id" => 21,
                "iata" => "3O",
                "icao" => "MAC",
                "name" => "Air Arabia Maroc"
            ],
            [
                "id" => 22,
                "iata" => "3S",
                "icao" => "BOX",
                "name" => "AeroLogic"
            ],
            [
                "id" => 23,
                "iata" => "3U",
                "icao" => "CSC",
                "name" => "Sichuan Airlines"
            ],
            [
                "id" => 24,
                "iata" => "3V",
                "icao" => "TAY",
                "name" => "ASL Airlines Belgium"
            ],
            [
                "id" => 25,
                "iata" => "3W",
                "icao" => "MWI",
                "name" => "Malawi Airlines"
            ],
            [
                "id" => 26,
                "iata" => "JC",
                "icao" => "JAC",
                "name" => "Japan Air Commuter"
            ],
            [
                "id" => 27,
                "iata" => "5V",
                "icao" => "VTS",
                "name" => "Everts Air Cargo"
            ],
            [
                "id" => 28,
                "iata" => "4J",
                "icao" => "JRC",
                "name" => "Jetair Caribbean"
            ],
            [
                "id" => 29,
                "iata" => "4A",
                "icao" => "AMP",
                "name" => "ATSA Airlines"
            ],
            [
                "id" => 30,
                "iata" => "IK",
                "icao" => "AKL",
                "name" => "Air Kiribati"
            ],
            [
                "id" => 31,
                "iata" => "4B",
                "icao" => "BTQ",
                "name" => "Boutique Air"
            ],
            [
                "id" => 32,
                "iata" => "4C",
                "icao" => "ARE",
                "name" => "LATAM Airlines Colombia"
            ],
            [
                "id" => 33,
                "iata" => "4D",
                "icao" => "ASD",
                "name" => "Air Sinai"
            ],
            [
                "id" => 34,
                "iata" => "Z3",
                "icao" => "TNR",
                "name" => "Tanana Air Service"
            ],
            [
                "id" => 35,
                "iata" => "4E",
                "icao" => "SBO",
                "name" => "Stabo Air"
            ],
            [
                "id" => 36,
                "iata" => "4G",
                "icao" => "GZP",
                "name" => "Gazpromavia"
            ],
            [
                "id" => 37,
                "iata" => "4M",
                "icao" => "DSM",
                "name" => "LATAM Airlines Argentina"
            ],
            [
                "id" => 38,
                "iata" => "4N",
                "icao" => "ANT",
                "name" => "Air North"
            ],
            [
                "id" => 39,
                "iata" => "RL",
                "icao" => "ABG",
                "name" => "Royal Flight Airlines"
            ],
            [
                "id" => 40,
                "iata" => "4S",
                "icao" => "OLC",
                "name" => "Solar Cargo"
            ],
            [
                "id" => 41,
                "iata" => "4U",
                "icao" => "GWI",
                "name" => "Germanwings"
            ],
            [
                "id" => 42,
                "iata" => "4W",
                "icao" => "AJK",
                "name" => "Allied Air"
            ],
            [
                "id" => 43,
                "iata" => "4X",
                "icao" => "MEC",
                "name" => "Mercury World Cargo"
            ],
            [
                "id" => 44,
                "iata" => "4Y",
                "icao" => "BGA",
                "name" => "Airbus Transport International"
            ],
            [
                "id" => 45,
                "iata" => "4Z",
                "icao" => "LNK",
                "name" => "Airlink"
            ],
            [
                "id" => 46,
                "iata" => "5A",
                "icao" => "AIP",
                "name" => "Alpine Air Express"
            ],
            [
                "id" => 47,
                "iata" => "5C",
                "icao" => "ICL",
                "name" => "C.A.L. Cargo Airlines"
            ],
            [
                "id" => 48,
                "iata" => "5D",
                "icao" => "SLI",
                "name" => "AeroMexico Connect"
            ],
            [
                "id" => 49,
                "iata" => "5H",
                "icao" => "FFV",
                "name" => "Fly540"
            ],
            [
                "id" => 50,
                "iata" => "5J",
                "icao" => "CEB",
                "name" => "Cebu Pacific Air"
            ],
            [
                "id" => 51,
                "iata" => "5M",
                "icao" => "MDM",
                "name" => "MedOps"
            ],
            [
                "id" => 52,
                "iata" => "5N",
                "icao" => "AUL",
                "name" => "Smartavia"
            ],
            [
                "id" => 53,
                "iata" => "5O",
                "icao" => "FPO",
                "name" => "ASL Airlines France"
            ],
            [
                "id" => 54,
                "iata" => "5S",
                "icao" => "GAK",
                "name" => "Global Air Transport"
            ],
            [
                "id" => 55,
                "iata" => "5T",
                "icao" => "MPE",
                "name" => "Canadian North"
            ],
            [
                "id" => 56,
                "iata" => "5X",
                "icao" => "UPS",
                "name" => "UPS"
            ],
            [
                "id" => 57,
                "iata" => "5Y",
                "icao" => "GTI",
                "name" => "Atlas Air"
            ],
            [
                "id" => 58,
                "iata" => "BH",
                "icao" => "BML",
                "name" => "Bismillah Airlines"
            ],
            [
                "id" => 59,
                "iata" => "6A",
                "icao" => "AMW",
                "name" => "Armenia Airways"
            ],
            [
                "id" => 60,
                "iata" => "6B",
                "icao" => "BLX",
                "name" => "TUIfly Nordic"
            ],
            [
                "id" => 61,
                "iata" => "6E",
                "icao" => "IGO",
                "name" => "IndiGo"
            ],
            [
                "id" => 62,
                "iata" => "9X",
                "icao" => "FDY",
                "name" => "Southern Airways Express"
            ],
            [
                "id" => 63,
                "iata" => "6G",
                "icao" => "RLX",
                "name" => "Go2Sky"
            ],
            [
                "id" => 64,
                "iata" => "6H",
                "icao" => "ISR",
                "name" => "Israir Airlines"
            ],
            [
                "id" => 65,
                "iata" => "6I",
                "icao" => "MMD",
                "name" => "Air Alsie"
            ],
            [
                "id" => 66,
                "iata" => "6Q",
                "icao" => "SAW",
                "name" => "Cham Wings Airlines"
            ],
            [
                "id" => 67,
                "iata" => "6R",
                "icao" => "DRU",
                "name" => "Mirny Air Enterprise"
            ],
            [
                "id" => 68,
                "iata" => "6S",
                "icao" => "SGQ",
                "name" => "Saudi Gulf"
            ],
            [
                "id" => 69,
                "iata" => "6Y",
                "icao" => "ART",
                "name" => "SmartLynx Airlines"
            ],
            [
                "id" => 70,
                "iata" => "MY",
                "icao" => "MWG",
                "name" => "MASwings"
            ],
            [
                "id" => 71,
                "iata" => "7C",
                "icao" => "JJA",
                "name" => "Jeju Air"
            ],
            [
                "id" => 72,
                "iata" => "7E",
                "icao" => "AWU",
                "name" => "Sylt Air"
            ],
            [
                "id" => 73,
                "iata" => "7F",
                "icao" => "FAB",
                "name" => "First Air"
            ],
            [
                "id" => 74,
                "iata" => "7G",
                "icao" => "SFJ",
                "name" => "StarFlyer"
            ],
            [
                "id" => 75,
                "iata" => "7H",
                "icao" => "RVF",
                "name" => "Ravn Alaska"
            ],
            [
                "id" => 76,
                "iata" => "7I",
                "icao" => "TLR",
                "name" => "Air Libya"
            ],
            [
                "id" => 77,
                "iata" => "7J",
                "icao" => "TJK",
                "name" => "Tajik Air"
            ],
            [
                "id" => 78,
                "iata" => "7L",
                "icao" => "AZG",
                "name" => "Silk Way West"
            ],
            [
                "id" => 79,
                "iata" => "7O",
                "icao" => "TVL",
                "name" => "Smartwings Hungary Kft"
            ],
            [
                "id" => 80,
                "iata" => "7Q",
                "icao" => "MNU",
                "name" => "Elite Airways"
            ],
            [
                "id" => 81,
                "iata" => "7R",
                "icao" => "RLU",
                "name" => "RusLine"
            ],
            [
                "id" => 82,
                "iata" => "7S",
                "icao" => "RYA",
                "name" => "Ryan Air"
            ],
            [
                "id" => 83,
                "iata" => "7V",
                "icao" => "FDR",
                "name" => "Federal Air"
            ],
            [
                "id" => 84,
                "iata" => "7W",
                "icao" => "WRC",
                "name" => "Wind Rose Aviation Company"
            ],
            [
                "id" => 85,
                "iata" => "7Y",
                "icao" => "MYP",
                "name" => "Mann Yadanarpon Airlines"
            ],
            [
                "id" => 86,
                "iata" => "7Z",
                "icao" => "AJI",
                "name" => "Ameristar Jet Charter"
            ],
            [
                "id" => 87,
                "iata" => "8D",
                "icao" => "EXV",
                "name" => "FitsAir"
            ],
            [
                "id" => 88,
                "iata" => "8E",
                "icao" => "EFX",
                "name" => "Easy Fly Express"
            ],
            [
                "id" => 89,
                "iata" => "8F",
                "icao" => "STP",
                "name" => "STP Airways"
            ],
            [
                "id" => 90,
                "iata" => "8K",
                "icao" => "KMI",
                "name" => "K-Mile Air"
            ],
            [
                "id" => 91,
                "iata" => "8L",
                "icao" => "LKE",
                "name" => "Lucky Air"
            ],
            [
                "id" => 92,
                "iata" => "8M",
                "icao" => "MMA",
                "name" => "Myanmar Airways International"
            ],
            [
                "id" => 93,
                "iata" => "8P",
                "icao" => "PCO",
                "name" => "Pacific Coastal Airlines"
            ],
            [
                "id" => 94,
                "iata" => "8T",
                "icao" => "TIN",
                "name" => "Air Tindi"
            ],
            [
                "id" => 95,
                "iata" => "8U",
                "icao" => "AAW",
                "name" => "Afriqiyah Airways"
            ],
            [
                "id" => 96,
                "iata" => "8V",
                "icao" => "ACP",
                "name" => "Astral Aviation Limited"
            ],
            [
                "id" => 97,
                "iata" => "CF",
                "icao" => "CIL",
                "name" => "CIAF"
            ],
            [
                "id" => 98,
                "iata" => "9C",
                "icao" => "CQH",
                "name" => "Spring Airlines"
            ],
            [
                "id" => 99,
                "iata" => "9D",
                "icao" => "NMG",
                "name" => "Genghis Khan Airlines"
            ],
            [
                "id" => 100,
                "iata" => "9E",
                "icao" => "EDV",
                "name" => "Endeavor Air"
            ],
            [
                "id" => 101,
                "iata" => "9H",
                "icao" => "CGN",
                "name" => "Air Changan"
            ],
            [
                "id" => 102,
                "iata" => "9J",
                "icao" => "DAN",
                "name" => "Dana Airlines"
            ],
            [
                "id" => 103,
                "iata" => "9K",
                "icao" => "KAP",
                "name" => "Cape Air"
            ],
            [
                "id" => 104,
                "iata" => "9M",
                "icao" => "GLR",
                "name" => "Central Mountain Air"
            ],
            [
                "id" => 105,
                "iata" => "9Q",
                "icao" => "CXE",
                "name" => "Caicos Express Airways"
            ],
            [
                "id" => 106,
                "iata" => "9R",
                "icao" => "NSE",
                "name" => "SATENA"
            ],
            [
                "id" => 107,
                "iata" => "9U",
                "icao" => "MLD",
                "name" => "Air Moldova"
            ],
            [
                "id" => 108,
                "iata" => "9V",
                "icao" => "ROI",
                "name" => "Avior Airlines"
            ],
            [
                "id" => 109,
                "iata" => "A3",
                "icao" => "AEE",
                "name" => "Aegean Airlines"
            ],
            [
                "id" => 110,
                "iata" => "A4",
                "icao" => "AZO",
                "name" => "Azimuth Airlines"
            ],
            [
                "id" => 111,
                "iata" => "A6",
                "icao" => "OTC",
                "name" => "Air Travel"
            ],
            [
                "id" => 112,
                "iata" => "A9",
                "icao" => "TGZ",
                "name" => "Georgian Airways"
            ],
            [
                "id" => 113,
                "iata" => "AA",
                "icao" => "AAL",
                "name" => "American Airlines"
            ],
            [
                "id" => 114,
                "iata" => "Q3",
                "icao" => "AXL",
                "name" => "Anguilla Air Services"
            ],
            [
                "id" => 115,
                "iata" => "8Y",
                "icao" => "AAV",
                "name" => "Pan Pacific Airlines"
            ],
            [
                "id" => 116,
                "iata" => "KF",
                "icao" => "ABB",
                "name" => "Air Belgium"
            ],
            [
                "id" => 117,
                "iata" => "PA",
                "icao" => "ABQ",
                "name" => "Airblue"
            ],
            [
                "id" => 118,
                "iata" => "AC",
                "icao" => "ACA",
                "name" => "Air Canada"
            ],
            [
                "id" => 119,
                "iata" => "AD",
                "icao" => "AZU",
                "name" => "Azul"
            ],
            [
                "id" => 120,
                "iata" => "AE",
                "icao" => "MDA",
                "name" => "Mandarin Airlines"
            ],
            [
                "id" => 121,
                "iata" => "NL",
                "icao" => "AEH",
                "name" => "Amelia International"
            ],
            [
                "id" => 122,
                "iata" => "AF",
                "icao" => "AFR",
                "name" => "Air France"
            ],
            [
                "id" => 123,
                "iata" => "AW",
                "icao" => "AFW",
                "name" => "Africa World Airlines"
            ],
            [
                "id" => 124,
                "iata" => "AG",
                "icao" => "ARU",
                "name" => "Aruba Airlines"
            ],
            [
                "id" => 125,
                "iata" => "AH",
                "icao" => "DAH",
                "name" => "Air Algerie"
            ],
            [
                "id" => 126,
                "iata" => "S8",
                "icao" => "SDA",
                "name" => "Sounds Air"
            ],
            [
                "id" => 127,
                "iata" => "MZ",
                "icao" => "AHX",
                "name" => "Amakusa Air"
            ],
            [
                "id" => 128,
                "iata" => "AI",
                "icao" => "AIC",
                "name" => "Air India"
            ],
            [
                "id" => 129,
                "iata" => "8R",
                "icao" => "AIA",
                "name" => "AMELIA"
            ],
            [
                "id" => 130,
                "iata" => "N2",
                "icao" => "NIG",
                "name" => "Aero Contractors"
            ],
            [
                "id" => 131,
                "iata" => "AJ",
                "icao" => "AZY",
                "name" => "Aztec Airways"
            ],
            [
                "id" => 132,
                "iata" => "AK",
                "icao" => "AXM",
                "name" => "AirAsia"
            ],
            [
                "id" => 133,
                "iata" => "AM",
                "icao" => "AMX",
                "name" => "Aeromexico"
            ],
            [
                "id" => 134,
                "iata" => "IM",
                "icao" => "JPJ",
                "name" => "Jupiter Jet"
            ],
            [
                "id" => 135,
                "iata" => "AN",
                "icao" => "WSN",
                "name" => "Advanced Air"
            ],
            [
                "id" => 136,
                "iata" => "YE",
                "icao" => "ANR",
                "name" => "YanAir"
            ],
            [
                "id" => 137,
                "iata" => "HP",
                "icao" => "APF",
                "name" => "Amapola"
            ],
            [
                "id" => 138,
                "iata" => "MM",
                "icao" => "APJ",
                "name" => "Peach Aviation"
            ],
            [
                "id" => 139,
                "iata" => "P4",
                "icao" => "APK",
                "name" => "Air Peace"
            ],
            [
                "id" => 140,
                "iata" => "YP",
                "icao" => "APZ",
                "name" => "Air Premia"
            ],
            [
                "id" => 141,
                "iata" => "AQ",
                "icao" => "JYH",
                "name" => "9 Air Co"
            ],
            [
                "id" => 142,
                "iata" => "AR",
                "icao" => "ARG",
                "name" => "Aerolineas Argentinas"
            ],
            [
                "id" => 143,
                "iata" => "RN",
                "icao" => "ARN",
                "name" => "Aeronexus"
            ],
            [
                "id" => 144,
                "iata" => "AS",
                "icao" => "ASA",
                "name" => "Alaska Airlines"
            ],
            [
                "id" => 145,
                "iata" => "AT",
                "icao" => "RAM",
                "name" => "Royal Air Maroc"
            ],
            [
                "id" => 146,
                "iata" => "F5",
                "icao" => "ATG",
                "name" => "Aerotranscargo"
            ],
            [
                "id" => 147,
                "iata" => "8C",
                "icao" => "ATN",
                "name" => "ATI"
            ],
            [
                "id" => 148,
                "iata" => "WP",
                "icao" => "ATW",
                "name" => "Air Antwerp"
            ],
            [
                "id" => 149,
                "iata" => "AV",
                "icao" => "AVA",
                "name" => "SA AVIANCA"
            ],
            [
                "id" => 150,
                "iata" => "ZT",
                "icao" => "AWC",
                "name" => "Titan Airways"
            ],
            [
                "id" => 151,
                "iata" => "A2",
                "icao" => "AWG",
                "name" => "Anima Wings"
            ],
            [
                "id" => 152,
                "iata" => "AX",
                "icao" => "AXY",
                "name" => "AIR X Charter"
            ],
            [
                "id" => 153,
                "iata" => "AY",
                "icao" => "FIN",
                "name" => "Finnair"
            ],
            [
                "id" => 154,
                "iata" => "Y6",
                "icao" => "AYD",
                "name" => "AB Aviation"
            ],
            [
                "id" => 155,
                "iata" => "AZ",
                "icao" => "AZA",
                "name" => "ITA"
            ],
            [
                "id" => 156,
                "iata" => "ZN",
                "icao" => "AZB",
                "name" => "Zambia Airways"
            ],
            [
                "id" => 157,
                "iata" => "ZP",
                "icao" => "AZQ",
                "name" => "Silk Way Airlines"
            ],
            [
                "id" => 158,
                "iata" => "B2",
                "icao" => "BRU",
                "name" => "Belavia"
            ],
            [
                "id" => 159,
                "iata" => "BF",
                "icao" => "FBU",
                "name" => "French Bee"
            ],
            [
                "id" => 160,
                "iata" => "B3",
                "icao" => "BTN",
                "name" => "Tashi Air Pvt Ltd"
            ],
            [
                "id" => 161,
                "iata" => "B5",
                "icao" => "EXZ",
                "name" => "Fly SAX"
            ],
            [
                "id" => 162,
                "iata" => "B6",
                "icao" => "JBU",
                "name" => "JetBlue Airways"
            ],
            [
                "id" => 163,
                "iata" => "B7",
                "icao" => "UIA",
                "name" => "UNI Air"
            ],
            [
                "id" => 164,
                "iata" => "B8",
                "icao" => "ERT",
                "name" => "Eritrean Airlines s.c."
            ],
            [
                "id" => 165,
                "iata" => "B9",
                "icao" => "IRB",
                "name" => "Iran Airtour"
            ],
            [
                "id" => 166,
                "iata" => "BA",
                "icao" => "BAW",
                "name" => "British Airways"
            ],
            [
                "id" => 167,
                "iata" => "BB",
                "icao" => "SBS",
                "name" => "Seaborne Airlines"
            ],
            [
                "id" => 168,
                "iata" => "BZ",
                "icao" => "BDA",
                "name" => "Blue Dart Aviation"
            ],
            [
                "id" => 169,
                "iata" => "BC",
                "icao" => "SKY",
                "name" => "Skymark Airlines"
            ],
            [
                "id" => 170,
                "iata" => "BO",
                "icao" => "BBD",
                "name" => "Bluebird Nordic"
            ],
            [
                "id" => 171,
                "iata" => "BM",
                "icao" => "BFO",
                "name" => "Bakhtar Afghan Airline"
            ],
            [
                "id" => 172,
                "iata" => "BG",
                "icao" => "BBC",
                "name" => "Biman Bangladesh Airlines"
            ],
            [
                "id" => 173,
                "iata" => "8H",
                "icao" => "BGH",
                "name" => "BH Air"
            ],
            [
                "id" => 174,
                "iata" => "BD",
                "icao" => "BGN",
                "name" => "Air Inter Transport"
            ],
            [
                "id" => 175,
                "iata" => "U4",
                "icao" => "BHA",
                "name" => "Buddha Air"
            ],
            [
                "id" => 176,
                "iata" => "BI",
                "icao" => "RBA",
                "name" => "Royal Brunei Airlines"
            ],
            [
                "id" => 177,
                "iata" => "BJ",
                "icao" => "LBT",
                "name" => "Nouvelair Tunisie"
            ],
            [
                "id" => 178,
                "iata" => "BK",
                "icao" => "OKA",
                "name" => "Okay Airways"
            ],
            [
                "id" => 179,
                "iata" => "BL",
                "icao" => "PIC",
                "name" => "Pacific Airlines"
            ],
            [
                "id" => 180,
                "iata" => "NB",
                "icao" => "BNL",
                "name" => "Berniq Airways"
            ],
            [
                "id" => 181,
                "iata" => "BP",
                "icao" => "BOT",
                "name" => "Air Botswana"
            ],
            [
                "id" => 182,
                "iata" => "RP",
                "icao" => "BPS",
                "name" => "Base Kft"
            ],
            [
                "id" => 183,
                "iata" => "BR",
                "icao" => "EVA",
                "name" => "EVA Air"
            ],
            [
                "id" => 184,
                "iata" => "BS",
                "icao" => "UBG",
                "name" => "US-Bangla Airlines"
            ],
            [
                "id" => 185,
                "iata" => "KW",
                "icao" => "BSC",
                "name" => "AeroStan"
            ],
            [
                "id" => 186,
                "iata" => "LL",
                "icao" => "BSK",
                "name" => "Miami Air International"
            ],
            [
                "id" => 187,
                "iata" => "5B",
                "icao" => "BSX",
                "name" => "Bassaka Air"
            ],
            [
                "id" => 188,
                "iata" => "BT",
                "icao" => "BTI",
                "name" => "Air Baltic"
            ],
            [
                "id" => 189,
                "iata" => "H6",
                "icao" => "HAG",
                "name" => "Ravn Connect"
            ],
            [
                "id" => 190,
                "iata" => "BV",
                "icao" => "BPA",
                "name" => "Luke Air"
            ],
            [
                "id" => 191,
                "iata" => "LB",
                "icao" => "BVL",
                "name" => "Bul Air"
            ],
            [
                "id" => 192,
                "iata" => "BW",
                "icao" => "BWA",
                "name" => "Caribbean Airlines"
            ],
            [
                "id" => 193,
                "iata" => "BX",
                "icao" => "ABL",
                "name" => "Air Busan"
            ],
            [
                "id" => 194,
                "iata" => "C2",
                "icao" => "CEL",
                "name" => "CEIBA Intercontinental"
            ],
            [
                "id" => 195,
                "iata" => "C4",
                "icao" => "QAI",
                "name" => "Conquest Air"
            ],
            [
                "id" => 196,
                "iata" => "C5",
                "icao" => "UCA",
                "name" => "CommutAir"
            ],
            [
                "id" => 197,
                "iata" => "C7",
                "icao" => "CIN",
                "name" => "Cinnamon Air"
            ],
            [
                "id" => 198,
                "iata" => "C8",
                "icao" => "CRA",
                "name" => "Cronos Airlines"
            ],
            [
                "id" => 199,
                "iata" => "CA",
                "icao" => "CCA",
                "name" => "Air China LTD"
            ],
            [
                "id" => 200,
                "iata" => "QC",
                "icao" => "CRC",
                "name" => "Camair-Co"
            ],
            [
                "id" => 201,
                "iata" => "XC",
                "icao" => "CAI",
                "name" => "Corendon Air"
            ],
            [
                "id" => 202,
                "iata" => "CC",
                "icao" => "OMT",
                "name" => "CM Airlines"
            ],
            [
                "id" => 203,
                "iata" => "9I",
                "icao" => "LLR",
                "name" => "Alliance Air"
            ],
            [
                "id" => 204,
                "iata" => "OT",
                "icao" => "CDO",
                "name" => "Tchadia Airlines"
            ],
            [
                "id" => 205,
                "iata" => "CE",
                "icao" => "CLG",
                "name" => "Route de Caen"
            ],
            [
                "id" => 206,
                "iata" => "Y2",
                "icao" => "CEY",
                "name" => "Air Century"
            ],
            [
                "id" => 207,
                "iata" => "A7",
                "icao" => "CFV",
                "name" => "Calafia Airlines"
            ],
            [
                "id" => 208,
                "iata" => "CG",
                "icao" => "TOK",
                "name" => "PNG Air"
            ],
            [
                "id" => 209,
                "iata" => "GT",
                "icao" => "CGH",
                "name" => "Air Guilin"
            ],
            [
                "id" => 210,
                "iata" => "GY",
                "icao" => "CGZ",
                "name" => "Colorful Guizhou Airlines"
            ],
            [
                "id" => 211,
                "iata" => "CH",
                "icao" => "BMJ",
                "name" => "Bemidji Aviation"
            ],
            [
                "id" => 212,
                "iata" => "CI",
                "icao" => "CAL",
                "name" => "China Airlines"
            ],
            [
                "id" => 213,
                "iata" => "SA",
                "icao" => "SAA",
                "name" => "South African Airways"
            ],
            [
                "id" => 214,
                "iata" => "CJ",
                "icao" => "CFE",
                "name" => "BA Cityflyer"
            ],
            [
                "id" => 215,
                "iata" => "CK",
                "icao" => "CKK",
                "name" => "China Cargo"
            ],
            [
                "id" => 216,
                "iata" => "CL",
                "icao" => "CLH",
                "name" => "Lufthansa CityLine"
            ],
            [
                "id" => 217,
                "iata" => "CM",
                "icao" => "PHP",
                "name" => "Psi Air 2007"
            ],
            [
                "id" => 218,
                "iata" => "CN",
                "icao" => "GDC",
                "name" => "Grand China Air"
            ],
            [
                "id" => 219,
                "iata" => "CD",
                "icao" => "CND",
                "name" => "Corendon Dutch Airlines"
            ],
            [
                "id" => 220,
                "iata" => "CO",
                "icao" => "CNW",
                "name" => "North-Western Cargo International Airlines"
            ],
            [
                "id" => 221,
                "iata" => "CP",
                "icao" => "LSI",
                "name" => "AlisCargo Airlines"
            ],
            [
                "id" => 222,
                "iata" => "EF",
                "icao" => "CPR",
                "name" => "AerCaribe Peru"
            ],
            [
                "id" => 223,
                "iata" => "CQ",
                "icao" => "CSV",
                "name" => "Coastal Aviation"
            ],
            [
                "id" => 224,
                "iata" => "OQ",
                "icao" => "CQN",
                "name" => "Chongqing Airlines"
            ],
            [
                "id" => 225,
                "iata" => "CT",
                "icao" => "CYL",
                "name" => "Alitalia CityLiner"
            ],
            [
                "id" => 226,
                "iata" => "QG",
                "icao" => "CTV",
                "name" => "Citilink"
            ],
            [
                "id" => 227,
                "iata" => "C3",
                "icao" => "TDR",
                "name" => "Trade Air"
            ],
            [
                "id" => 228,
                "iata" => "CU",
                "icao" => "CUB",
                "name" => "Cubana de Aviacion"
            ],
            [
                "id" => 229,
                "iata" => "CV",
                "icao" => "CLX",
                "name" => "Cargolux"
            ],
            [
                "id" => 230,
                "iata" => "3C",
                "icao" => "CVA",
                "name" => "Air Chathams"
            ],
            [
                "id" => 231,
                "iata" => "CX",
                "icao" => "CPA",
                "name" => "Cathay Pacific"
            ],
            [
                "id" => 232,
                "iata" => "CS",
                "icao" => "CXB",
                "name" => "Comlux Aruba"
            ],
            [
                "id" => 233,
                "iata" => "XR",
                "icao" => "CXI",
                "name" => "Corendon Airlines Europe"
            ],
            [
                "id" => 234,
                "iata" => "CY",
                "icao" => "CYP",
                "name" => "Cyprus Airways"
            ],
            [
                "id" => 235,
                "iata" => "U8",
                "icao" => "CYF",
                "name" => "Tus Airways"
            ],
            [
                "id" => 236,
                "iata" => "ZO",
                "icao" => "CYN",
                "name" => "Colorful Yunnan Airlines"
            ],
            [
                "id" => 237,
                "iata" => "CZ",
                "icao" => "CSN",
                "name" => "China Southern Airlines"
            ],
            [
                "id" => 238,
                "iata" => "D0",
                "icao" => "DHK",
                "name" => "DHL Air"
            ],
            [
                "id" => 239,
                "iata" => "D2",
                "icao" => "SSF",
                "name" => "Severstal Aircompany"
            ],
            [
                "id" => 240,
                "iata" => "D3",
                "icao" => "DAO",
                "name" => "Daallo Airlines"
            ],
            [
                "id" => 241,
                "iata" => "D4",
                "icao" => "GEL",
                "name" => "Airline GEO SKY"
            ],
            [
                "id" => 242,
                "iata" => "D5",
                "icao" => "DAE",
                "name" => "DHL Aero Expreso"
            ],
            [
                "id" => 243,
                "iata" => "D6",
                "icao" => "GCA",
                "name" => "GECA"
            ],
            [
                "id" => 244,
                "iata" => "D7",
                "icao" => "XAX",
                "name" => "AirAsia X"
            ],
            [
                "id" => 245,
                "iata" => "D8",
                "icao" => "IBK",
                "name" => "Norwegian Air International"
            ],
            [
                "id" => 246,
                "iata" => "D9",
                "icao" => "DMQ",
                "name" => "Daallo Airlines (Somalia)"
            ],
            [
                "id" => 247,
                "iata" => "V5",
                "icao" => "DAP",
                "name" => "Aerovias DAP"
            ],
            [
                "id" => 248,
                "iata" => "TF",
                "icao" => "BRX",
                "name" => "Braathens Regional"
            ],
            [
                "id" => 249,
                "iata" => "DD",
                "icao" => "NOK",
                "name" => "Nok Air"
            ],
            [
                "id" => 250,
                "iata" => "DE",
                "icao" => "CFG",
                "name" => "Condor"
            ],
            [
                "id" => 251,
                "iata" => "DG",
                "icao" => "SRQ",
                "name" => "Cebgo"
            ],
            [
                "id" => 252,
                "iata" => "DH",
                "icao" => "NAN",
                "name" => "Norwegian Air Norway"
            ],
            [
                "id" => 253,
                "iata" => "DI",
                "icao" => "NRS",
                "name" => "Norwegian Air UK"
            ],
            [
                "id" => 254,
                "iata" => "B0",
                "icao" => "DJT",
                "name" => "La Compagnie"
            ],
            [
                "id" => 255,
                "iata" => "DK",
                "icao" => "VKG",
                "name" => "Sunclass Airlines"
            ],
            [
                "id" => 256,
                "iata" => "DL",
                "icao" => "DAL",
                "name" => "Delta Air Lines"
            ],
            [
                "id" => 257,
                "iata" => "DN",
                "icao" => "NAA",
                "name" => "Norwegian Air Argentina"
            ],
            [
                "id" => 258,
                "iata" => "DP",
                "icao" => "PBD",
                "name" => "Pobeda"
            ],
            [
                "id" => 259,
                "iata" => "DR",
                "icao" => "RLH",
                "name" => "Ruili Airlines"
            ],
            [
                "id" => 260,
                "iata" => "DS",
                "icao" => "EZS",
                "name" => "Easyjet Switzerland"
            ],
            [
                "id" => 261,
                "iata" => "DT",
                "icao" => "DTA",
                "name" => "TAAG-Angola Airlines"
            ],
            [
                "id" => 262,
                "iata" => "DV",
                "icao" => "VSV",
                "name" => "SCAT Airlines"
            ],
            [
                "id" => 263,
                "iata" => "3R",
                "icao" => "DVR",
                "name" => "Divi Divi Air"
            ],
            [
                "id" => 264,
                "iata" => "DX",
                "icao" => "DTR",
                "name" => "Danish Air"
            ],
            [
                "id" => 265,
                "iata" => "DY",
                "icao" => "NAX",
                "name" => "Norwegian Air Shuttle"
            ],
            [
                "id" => 266,
                "iata" => "E3",
                "icao" => "EGW",
                "name" => "Ego Airways"
            ],
            [
                "id" => 267,
                "iata" => "E6",
                "icao" => "BCT",
                "name" => "Bringer Air Cargo Taxi"
            ],
            [
                "id" => 268,
                "iata" => "E7",
                "icao" => "EKA",
                "name" => "Equaflight Service"
            ],
            [
                "id" => 269,
                "iata" => "EB",
                "icao" => "PLM",
                "name" => "Wamos Air"
            ],
            [
                "id" => 270,
                "iata" => "8J",
                "icao" => "ECO",
                "name" => "EcoJet"
            ],
            [
                "id" => 271,
                "iata" => "ED",
                "icao" => "AXE",
                "name" => "Air Explore"
            ],
            [
                "id" => 272,
                "iata" => "8W",
                "icao" => "EDR",
                "name" => "Fly All Ways"
            ],
            [
                "id" => 273,
                "iata" => "WK",
                "icao" => "EDW",
                "name" => "Edelweiss Air"
            ],
            [
                "id" => 274,
                "iata" => "EE",
                "icao" => "EST",
                "name" => "Xfly"
            ],
            [
                "id" => 275,
                "iata" => "VE",
                "icao" => "EFY",
                "name" => "EasyFly"
            ],
            [
                "id" => 276,
                "iata" => "Y9",
                "icao" => "DAT",
                "name" => "Enerjet"
            ],
            [
                "id" => 277,
                "iata" => "EH",
                "icao" => "AKX",
                "name" => "ANA Wings"
            ],
            [
                "id" => 278,
                "iata" => "EI",
                "icao" => "EIN",
                "name" => "Aer Lingus"
            ],
            [
                "id" => 279,
                "iata" => "EC",
                "icao" => "EJU",
                "name" => "EasyJet Europe"
            ],
            [
                "id" => 280,
                "iata" => "EK",
                "icao" => "UAE",
                "name" => "Emirates"
            ],
            [
                "id" => 281,
                "iata" => "EL",
                "icao" => "ELB",
                "name" => "Ellinair"
            ],
            [
                "id" => 282,
                "iata" => "EM",
                "icao" => "CFS",
                "name" => "Empire Airlines"
            ],
            [
                "id" => 283,
                "iata" => "EN",
                "icao" => "DLA",
                "name" => "Air Dolomiti"
            ],
            [
                "id" => 284,
                "iata" => "E4",
                "icao" => "ENT",
                "name" => "Enter Air"
            ],
            [
                "id" => 285,
                "iata" => "EP",
                "icao" => "IRC",
                "name" => "Iran Aseman Airlines"
            ],
            [
                "id" => 286,
                "iata" => "DZ",
                "icao" => "EPA",
                "name" => "Donghai Airlines"
            ],
            [
                "id" => 287,
                "iata" => "ES",
                "icao" => "ETR",
                "name" => "Estelar"
            ],
            [
                "id" => 288,
                "iata" => "ET",
                "icao" => "EMT",
                "name" => "Emetebe S.A."
            ],
            [
                "id" => 289,
                "iata" => "EU",
                "icao" => "UEA",
                "name" => "Chengdu Airlines"
            ],
            [
                "id" => 290,
                "iata" => "EV",
                "icao" => "ASQ",
                "name" => "ExpressJet"
            ],
            [
                "id" => 291,
                "iata" => "E9",
                "icao" => "EVE",
                "name" => "Iberojet Airlines"
            ],
            [
                "id" => 292,
                "iata" => "EW",
                "icao" => "EWG",
                "name" => "Eurowings"
            ],
            [
                "id" => 293,
                "iata" => "E2",
                "icao" => "EWE",
                "name" => "Eurowings Europe"
            ],
            [
                "id" => 294,
                "iata" => "ZD",
                "icao" => "EWR",
                "name" => "EWA Air"
            ],
            [
                "id" => 295,
                "iata" => "EY",
                "icao" => "ETD",
                "name" => "Etihad Airways"
            ],
            [
                "id" => 296,
                "iata" => "EZ",
                "icao" => "SUS",
                "name" => "Sun-Air"
            ],
            [
                "id" => 297,
                "iata" => "F0",
                "icao" => "FJR",
                "name" => "Fly Jordan"
            ],
            [
                "id" => 298,
                "iata" => "F2",
                "icao" => "XLK",
                "name" => "Safarilink"
            ],
            [
                "id" => 299,
                "iata" => "F8",
                "icao" => "FLE",
                "name" => "Flair Airlines"
            ],
            [
                "id" => 300,
                "iata" => "F9",
                "icao" => "FFT",
                "name" => "Frontier Airlines"
            ],
            [
                "id" => 301,
                "iata" => "FA",
                "icao" => "SFR",
                "name" => "Safair"
            ],
            [
                "id" => 302,
                "iata" => "F3",
                "icao" => "FAD",
                "name" => "flyadeal"
            ],
            [
                "id" => 303,
                "iata" => "FB",
                "icao" => "LZB",
                "name" => "Bulgaria Air"
            ],
            [
                "id" => 304,
                "iata" => "VF",
                "icao" => "FBB",
                "name" => "Fly Armenia Airways"
            ],
            [
                "id" => 305,
                "iata" => "6W",
                "icao" => "FBS",
                "name" => "FlyBosnia"
            ],
            [
                "id" => 306,
                "iata" => "N7",
                "icao" => "NEP",
                "name" => "My Jet Xpress Airlines"
            ],
            [
                "id" => 307,
                "iata" => "FD",
                "icao" => "AIQ",
                "name" => "Thai AirAsia"
            ],
            [
                "id" => 308,
                "iata" => "JH",
                "icao" => "FDA",
                "name" => "Fuji Dream Airlines"
            ],
            [
                "id" => 309,
                "iata" => "4F",
                "icao" => "FDT",
                "name" => "Freedom Airline Express"
            ],
            [
                "id" => 310,
                "iata" => "FG",
                "icao" => "AFG",
                "name" => "Ariana Afghan Airlines"
            ],
            [
                "id" => 311,
                "iata" => "4V",
                "icao" => "FGW",
                "name" => "Fly Gangwon"
            ],
            [
                "id" => 312,
                "iata" => "FH",
                "icao" => "FHY",
                "name" => "Freebird Airlines"
            ],
            [
                "id" => 313,
                "iata" => "FI",
                "icao" => "ICE",
                "name" => "Icelandair"
            ],
            [
                "id" => 314,
                "iata" => "5F",
                "icao" => "FIA",
                "name" => "Fly One"
            ],
            [
                "id" => 315,
                "iata" => "FJ",
                "icao" => "FJI",
                "name" => "Fiji Airways"
            ],
            [
                "id" => 316,
                "iata" => "FN",
                "icao" => "FJW",
                "name" => "Fastjet Zimbabwe"
            ],
            [
                "id" => 317,
                "iata" => "FM",
                "icao" => "CSH",
                "name" => "Shanghai Airlines"
            ],
            [
                "id" => 318,
                "iata" => "ML",
                "icao" => "FML",
                "name" => "Sky Mali"
            ],
            [
                "id" => 319,
                "iata" => "FO",
                "icao" => "FBZ",
                "name" => "Flybondi"
            ],
            [
                "id" => 320,
                "iata" => "FS",
                "icao" => "FOX",
                "name" => "Flyr AS"
            ],
            [
                "id" => 321,
                "iata" => "OG",
                "icao" => "SDG",
                "name" => "Star Air"
            ],
            [
                "id" => 322,
                "iata" => "FR",
                "icao" => "RYR",
                "name" => "Ryanair"
            ],
            [
                "id" => 323,
                "iata" => "X7",
                "icao" => "CHG",
                "name" => "Challenge Airlines"
            ],
            [
                "id" => 324,
                "iata" => "FT",
                "icao" => "FEG",
                "name" => "FlyEgypt"
            ],
            [
                "id" => 325,
                "iata" => "FU",
                "icao" => "FZA",
                "name" => "Fuzhou Airlines"
            ],
            [
                "id" => 326,
                "iata" => "FV",
                "icao" => "SDM",
                "name" => "Rossiya Airlines"
            ],
            [
                "id" => 327,
                "iata" => "FW",
                "icao" => "MFJ",
                "name" => "Solenta Aviation Mozambique"
            ],
            [
                "id" => 328,
                "iata" => "FX",
                "icao" => "FDX",
                "name" => "FedEx"
            ],
            [
                "id" => 329,
                "iata" => "FY",
                "icao" => "FFM",
                "name" => "Firefly"
            ],
            [
                "id" => 330,
                "iata" => "FZ",
                "icao" => "FDB",
                "name" => "flydubai"
            ],
            [
                "id" => 331,
                "iata" => "G2",
                "icao" => "GBG",
                "name" => "Gullivair"
            ],
            [
                "id" => 332,
                "iata" => "GW",
                "icao" => "GJT",
                "name" => "GetJet Airlines"
            ],
            [
                "id" => 333,
                "iata" => "G3",
                "icao" => "GLO",
                "name" => "Gol"
            ],
            [
                "id" => 334,
                "iata" => "G4",
                "icao" => "AAY",
                "name" => "Allegiant Air"
            ],
            [
                "id" => 335,
                "iata" => "G5",
                "icao" => "HXA",
                "name" => "China Express Air"
            ],
            [
                "id" => 336,
                "iata" => "G7",
                "icao" => "GJS",
                "name" => "GoJet Airlines"
            ],
            [
                "id" => 337,
                "iata" => "G8",
                "icao" => "GOW",
                "name" => "Go First"
            ],
            [
                "id" => 338,
                "iata" => "G9",
                "icao" => "ABY",
                "name" => "Air Arabia"
            ],
            [
                "id" => 339,
                "iata" => "GA",
                "icao" => "GIA",
                "name" => "Garuda Indonesia"
            ],
            [
                "id" => 340,
                "iata" => "G0",
                "icao" => "GAL",
                "name" => "Albatros Airlines"
            ],
            [
                "id" => 341,
                "iata" => "GB",
                "icao" => "ABX",
                "name" => "ABX Air"
            ],
            [
                "id" => 342,
                "iata" => "6L",
                "icao" => "GCL",
                "name" => "CargoLogic Germany"
            ],
            [
                "id" => 343,
                "iata" => "LC",
                "icao" => "GCS",
                "name" => "Skyway"
            ],
            [
                "id" => 344,
                "iata" => "DW",
                "icao" => "GDE",
                "name" => "Great Dane Airlines"
            ],
            [
                "id" => 345,
                "iata" => "GF",
                "icao" => "GFA",
                "name" => "Gulf Air"
            ],
            [
                "id" => 346,
                "iata" => "GG",
                "icao" => "KYE",
                "name" => "Sky Lease Cargo"
            ],
            [
                "id" => 347,
                "iata" => "GJ",
                "icao" => "CDC",
                "name" => "Loong Air"
            ],
            [
                "id" => 348,
                "iata" => "GK",
                "icao" => "JJP",
                "name" => "Jetstar Japan"
            ],
            [
                "id" => 349,
                "iata" => "GL",
                "icao" => "GRL",
                "name" => "Air Greenland"
            ],
            [
                "id" => 350,
                "iata" => "GM",
                "icao" => "TMG",
                "name" => "Tri-MG Intra Asia Airlines"
            ],
            [
                "id" => 351,
                "iata" => "GO",
                "icao" => "KZU",
                "name" => "ULS Airlines Cargo"
            ],
            [
                "id" => 352,
                "iata" => "IV",
                "icao" => "GPX",
                "name" => "GP Aviation"
            ],
            [
                "id" => 353,
                "iata" => "GQ",
                "icao" => "SEH",
                "name" => "Sky Express"
            ],
            [
                "id" => 354,
                "iata" => "GR",
                "icao" => "AUR",
                "name" => "Aurigny Air Services"
            ],
            [
                "id" => 355,
                "iata" => "GS",
                "icao" => "GCR",
                "name" => "Tianjin Airlines"
            ],
            [
                "id" => 356,
                "iata" => "GH",
                "icao" => "GTR",
                "name" => "Galistair Trading Ltd"
            ],
            [
                "id" => 357,
                "iata" => "R2",
                "icao" => "GTS",
                "name" => "Groupe Transair"
            ],
            [
                "id" => 358,
                "iata" => "GU",
                "icao" => "GUG",
                "name" => "Aviateca Guatemala"
            ],
            [
                "id" => 359,
                "iata" => "R8",
                "icao" => "GUC",
                "name" => "Puer General Aviation"
            ],
            [
                "id" => 360,
                "iata" => "GV",
                "icao" => "GUN",
                "name" => "Grant Aviation"
            ],
            [
                "id" => 361,
                "iata" => "U5",
                "icao" => "GWR",
                "name" => "Gowair"
            ],
            [
                "id" => 362,
                "iata" => "GX",
                "icao" => "CBG",
                "name" => "Guangxi Beibu Gulf Airlines"
            ],
            [
                "id" => 363,
                "iata" => "H2",
                "icao" => "SKU",
                "name" => "Sky Airline"
            ],
            [
                "id" => 364,
                "iata" => "HT",
                "icao" => "HAT",
                "name" => "Air Horizont"
            ],
            [
                "id" => 365,
                "iata" => "HS",
                "icao" => "HLI",
                "name" => "Heli Securite"
            ],
            [
                "id" => 366,
                "iata" => "H8",
                "icao" => "SKX",
                "name" => "Sky Airlines Peru"
            ],
            [
                "id" => 367,
                "iata" => "H9",
                "icao" => "HIM",
                "name" => "Himalaya Airlines"
            ],
            [
                "id" => 368,
                "iata" => "HA",
                "icao" => "HYA",
                "name" => "Al Haya Aviation"
            ],
            [
                "id" => 369,
                "iata" => "HD",
                "icao" => "ADO",
                "name" => "Air Do"
            ],
            [
                "id" => 370,
                "iata" => "5Q",
                "icao" => "HES",
                "name" => "Holiday Europe"
            ],
            [
                "id" => 371,
                "iata" => "5K",
                "icao" => "HFY",
                "name" => "Hi Fly"
            ],
            [
                "id" => 372,
                "iata" => "HG",
                "icao" => "HBN",
                "name" => "Hibernian Airlines"
            ],
            [
                "id" => 373,
                "iata" => "4H",
                "icao" => "HGG",
                "name" => "Hi Air"
            ],
            [
                "id" => 374,
                "iata" => "HJ",
                "icao" => "TMN",
                "name" => "Tasman Cargo Airlines"
            ],
            [
                "id" => 375,
                "iata" => "HM",
                "icao" => "SEY",
                "name" => "Air Seychelles"
            ],
            [
                "id" => 376,
                "iata" => "HO",
                "icao" => "DKH",
                "name" => "Juneyao Airlines"
            ],
            [
                "id" => 377,
                "iata" => "A5",
                "icao" => "HOP",
                "name" => "HOP!"
            ],
            [
                "id" => 378,
                "iata" => "HR",
                "icao" => "HHN",
                "name" => "Hahn Air"
            ],
            [
                "id" => 379,
                "iata" => "HN",
                "icao" => "HST",
                "name" => "UAB Heston Airlines"
            ],
            [
                "id" => 380,
                "iata" => "HU",
                "icao" => "CHH",
                "name" => "Hainan Airlines"
            ],
            [
                "id" => 381,
                "iata" => "HV",
                "icao" => "TRA",
                "name" => "Transavia"
            ],
            [
                "id" => 382,
                "iata" => "HW",
                "icao" => "NWL",
                "name" => "North-Wright Airways"
            ],
            [
                "id" => 383,
                "iata" => "HX",
                "icao" => "CRK",
                "name" => "Hong Kong Airlines"
            ],
            [
                "id" => 384,
                "iata" => "HY",
                "icao" => "UZB",
                "name" => "Uzbekistan Airways"
            ],
            [
                "id" => 385,
                "iata" => "H7",
                "icao" => "HYM",
                "name" => "CA HiSky"
            ],
            [
                "id" => 386,
                "iata" => "H4",
                "icao" => "HYS",
                "name" => "HiSky Europe"
            ],
            [
                "id" => 387,
                "iata" => "HZ",
                "icao" => "SHU",
                "name" => "Aurora"
            ],
            [
                "id" => 388,
                "iata" => "I2",
                "icao" => "IBS",
                "name" => "Iberia Express"
            ],
            [
                "id" => 389,
                "iata" => "I4",
                "icao" => "EXP",
                "name" => "Island Air Express"
            ],
            [
                "id" => 390,
                "iata" => "I5",
                "icao" => "IAD",
                "name" => "AirAsia India"
            ],
            [
                "id" => 391,
                "iata" => "I8",
                "icao" => "IZA",
                "name" => "Izhavia"
            ],
            [
                "id" => 392,
                "iata" => "I9",
                "icao" => "HLF",
                "name" => "China Central Airlines"
            ],
            [
                "id" => 393,
                "iata" => "IA",
                "icao" => "IAW",
                "name" => "I A W"
            ],
            [
                "id" => 394,
                "iata" => "IO",
                "icao" => "IAE",
                "name" => "IrAero"
            ],
            [
                "id" => 395,
                "iata" => "QI",
                "icao" => "IAN",
                "name" => "Ibom Air"
            ],
            [
                "id" => 396,
                "iata" => "IB",
                "icao" => "IBE",
                "name" => "Iberia"
            ],
            [
                "id" => 397,
                "iata" => "ID",
                "icao" => "BTK",
                "name" => "Batik Air"
            ],
            [
                "id" => 398,
                "iata" => "IE",
                "icao" => "SOL",
                "name" => "Solomon Airlines"
            ],
            [
                "id" => 399,
                "iata" => "IF",
                "icao" => "TSU",
                "name" => "Gulf and Caribbean Cargo"
            ],
            [
                "id" => 400,
                "iata" => "IG",
                "icao" => "ISS",
                "name" => "Air Italy"
            ],
            [
                "id" => 401,
                "iata" => "FE",
                "icao" => "IHO",
                "name" => "748 Air Services"
            ],
            [
                "id" => 402,
                "iata" => "II",
                "icao" => "CSQ",
                "name" => "IBC Airways"
            ],
            [
                "id" => 403,
                "iata" => "K8",
                "icao" => "IJW",
                "name" => "InterJet West"
            ],
            [
                "id" => 404,
                "iata" => "2G",
                "icao" => "AGU",
                "name" => "Angara Airlines"
            ],
            [
                "id" => 405,
                "iata" => "EO",
                "icao" => "KAR",
                "name" => "Pegas Fly"
            ],
            [
                "id" => 406,
                "iata" => "XM",
                "icao" => "IMX",
                "name" => "Zimex Aviation"
            ],
            [
                "id" => 407,
                "iata" => "IN",
                "icao" => "LKN",
                "name" => "Nam Air"
            ],
            [
                "id" => 408,
                "iata" => "IQ",
                "icao" => "QAZ",
                "name" => "Qazaq Air"
            ],
            [
                "id" => 409,
                "iata" => "IR",
                "icao" => "IRA",
                "name" => "Iran Air"
            ],
            [
                "id" => 410,
                "iata" => "QB",
                "icao" => "QSM",
                "name" => "Qeshm Air"
            ],
            [
                "id" => 411,
                "iata" => "T6",
                "icao" => "ATX",
                "name" => "AirSWIFT"
            ],
            [
                "id" => 412,
                "iata" => "8I",
                "icao" => "IPM",
                "name" => "ITA Transportes Aereos"
            ],
            [
                "id" => 413,
                "iata" => "IW",
                "icao" => "WON",
                "name" => "Wings Air (Indonesia)"
            ],
            [
                "id" => 414,
                "iata" => "IX",
                "icao" => "AXB",
                "name" => "AIR INDIA EXPRESS"
            ],
            [
                "id" => 415,
                "iata" => "IY",
                "icao" => "IYE",
                "name" => "Yemenia"
            ],
            [
                "id" => 416,
                "iata" => "IZ",
                "icao" => "AIZ",
                "name" => "Arkia Israeli Airlines"
            ],
            [
                "id" => 417,
                "iata" => "J2",
                "icao" => "AHY",
                "name" => "AZAL Azerbaijan Airlines"
            ],
            [
                "id" => 418,
                "iata" => "JX",
                "icao" => "SJX",
                "name" => "Starlux Airlines"
            ],
            [
                "id" => 419,
                "iata" => "J3",
                "icao" => "PLR",
                "name" => "Northwestern Air"
            ],
            [
                "id" => 420,
                "iata" => "J4",
                "icao" => "BDR",
                "name" => "Badr Airlines"
            ],
            [
                "id" => 421,
                "iata" => "J6",
                "icao" => "OPS",
                "name" => "Jet Ops"
            ],
            [
                "id" => 422,
                "iata" => "J7",
                "icao" => "ABS",
                "name" => "Afrijet"
            ],
            [
                "id" => 423,
                "iata" => "J8",
                "icao" => "BVT",
                "name" => "Berjaya Air"
            ],
            [
                "id" => 424,
                "iata" => "J9",
                "icao" => "JZR",
                "name" => "Jazeera Airways"
            ],
            [
                "id" => 425,
                "iata" => "TB",
                "icao" => "JAF",
                "name" => "TUI fly"
            ],
            [
                "id" => 426,
                "iata" => "JA",
                "icao" => "JAT",
                "name" => "JetSMART"
            ],
            [
                "id" => 427,
                "iata" => "R5",
                "icao" => "JAV",
                "name" => "Jordan Aviation Airlines"
            ],
            [
                "id" => 428,
                "iata" => "3J",
                "icao" => "JBW",
                "name" => "Jubba Airways (Kenya)"
            ],
            [
                "id" => 429,
                "iata" => "QD",
                "icao" => "JCC",
                "name" => "JC International Airlines"
            ],
            [
                "id" => 430,
                "iata" => "JD",
                "icao" => "CBJ",
                "name" => "Beijing Capital Airlines"
            ],
            [
                "id" => 431,
                "iata" => "JE",
                "icao" => "MNO",
                "name" => "Mango"
            ],
            [
                "id" => 432,
                "iata" => "OH",
                "icao" => "JIA",
                "name" => "PSA Airlines"
            ],
            [
                "id" => 433,
                "iata" => "JJ",
                "icao" => "TAM",
                "name" => "LATAM Airlines Brasil"
            ],
            [
                "id" => 434,
                "iata" => "JL",
                "icao" => "JTL",
                "name" => "Jet Linx Aviation"
            ],
            [
                "id" => 435,
                "iata" => "JM",
                "icao" => "JMA",
                "name" => "Jambojet Limited"
            ],
            [
                "id" => 436,
                "iata" => "JO",
                "icao" => "JNK",
                "name" => "Jonika"
            ],
            [
                "id" => 437,
                "iata" => "JQ",
                "icao" => "JST",
                "name" => "Jetstar"
            ],
            [
                "id" => 438,
                "iata" => "JR",
                "icao" => "JOY",
                "name" => "Joy Air"
            ],
            [
                "id" => 439,
                "iata" => "JS",
                "icao" => "KOR",
                "name" => "Air Koryo"
            ],
            [
                "id" => 440,
                "iata" => "JT",
                "icao" => "LNI",
                "name" => "Lion Air"
            ],
            [
                "id" => 441,
                "iata" => "JU",
                "icao" => "ASL",
                "name" => "Air Serbia"
            ],
            [
                "id" => 442,
                "iata" => "UJ",
                "icao" => "LMU",
                "name" => "AlMasria"
            ],
            [
                "id" => 443,
                "iata" => "JV",
                "icao" => "BLS",
                "name" => "Bearskin Airlines"
            ],
            [
                "id" => 444,
                "iata" => "JY",
                "icao" => "IWY",
                "name" => "interCaribbean Airways"
            ],
            [
                "id" => 445,
                "iata" => "K3",
                "icao" => "SAQ",
                "name" => "Safe Air Company"
            ],
            [
                "id" => 446,
                "iata" => "K4",
                "icao" => "CKS",
                "name" => "Kalitta Air"
            ],
            [
                "id" => 447,
                "iata" => "K6",
                "icao" => "KHV",
                "name" => "Cambodia Angkor Air"
            ],
            [
                "id" => 448,
                "iata" => "KB",
                "icao" => "DRK",
                "name" => "Royal Bhutan Airlines"
            ],
            [
                "id" => 449,
                "iata" => "K7",
                "icao" => "KBZ",
                "name" => "Air KZB Company Limited"
            ],
            [
                "id" => 450,
                "iata" => "KC",
                "icao" => "KZR",
                "name" => "Air Astana"
            ],
            [
                "id" => 451,
                "iata" => "KD",
                "icao" => "WGN",
                "name" => "Western Global"
            ],
            [
                "id" => 452,
                "iata" => "KE",
                "icao" => "KAL",
                "name" => "Korean Air"
            ],
            [
                "id" => 453,
                "iata" => "5Z",
                "icao" => "KEM",
                "name" => "CemAir"
            ],
            [
                "id" => 454,
                "iata" => "FK",
                "icao" => "KFA",
                "name" => "KF Cargo"
            ],
            [
                "id" => 455,
                "iata" => "K9",
                "icao" => "KFS",
                "name" => "Kalitta Charters"
            ],
            [
                "id" => 456,
                "iata" => "AB",
                "icao" => "KGS",
                "name" => "KAP.KG Aircompany"
            ],
            [
                "id" => 457,
                "iata" => "KH",
                "icao" => "AAH",
                "name" => "Aloha Air Cargo"
            ],
            [
                "id" => 458,
                "iata" => "DQ",
                "icao" => "KHH",
                "name" => "Alexandria Airlines"
            ],
            [
                "id" => 459,
                "iata" => "KJ",
                "icao" => "AIH",
                "name" => "Air Incheon"
            ],
            [
                "id" => 460,
                "iata" => "KL",
                "icao" => "KLM",
                "name" => "KLM"
            ],
            [
                "id" => 461,
                "iata" => "KM",
                "icao" => "AMC",
                "name" => "Air Malta"
            ],
            [
                "id" => 462,
                "iata" => "KO",
                "icao" => "AER",
                "name" => "ACE Air Cargo"
            ],
            [
                "id" => 463,
                "iata" => "KR",
                "icao" => "KME",
                "name" => "Cambodia Airways"
            ],
            [
                "id" => 464,
                "iata" => "KN",
                "icao" => "CUA",
                "name" => "China United Airlines"
            ],
            [
                "id" => 465,
                "iata" => "KP",
                "icao" => "SKK",
                "name" => "ASKY"
            ],
            [
                "id" => 466,
                "iata" => "KQ",
                "icao" => "KQA",
                "name" => "Kenya Airways"
            ],
            [
                "id" => 467,
                "iata" => "KT",
                "icao" => "HOG",
                "name" => "Mahogany Air"
            ],
            [
                "id" => 468,
                "iata" => "ZF",
                "icao" => "SXY",
                "name" => "Safari Express Cargo"
            ],
            [
                "id" => 469,
                "iata" => "KU",
                "icao" => "KAC",
                "name" => "Kuwait Airways"
            ],
            [
                "id" => 470,
                "iata" => "KX",
                "icao" => "CAY",
                "name" => "Cayman Airways"
            ],
            [
                "id" => 471,
                "iata" => "KY",
                "icao" => "KNA",
                "name" => "Kunming Airlines"
            ],
            [
                "id" => 472,
                "iata" => "KZ",
                "icao" => "NCA",
                "name" => "NCA - Nippon Cargo Airlines"
            ],
            [
                "id" => 473,
                "iata" => "LE",
                "icao" => "NSW",
                "name" => "Norwegian Air Sweden"
            ],
            [
                "id" => 474,
                "iata" => "L2",
                "icao" => "LYC",
                "name" => "Lynden Air Cargo"
            ],
            [
                "id" => 475,
                "iata" => "LT",
                "icao" => "SNG",
                "name" => "LongJiang Airlines"
            ],
            [
                "id" => 476,
                "iata" => "LW",
                "icao" => "LDA",
                "name" => "Lauda Europe"
            ],
            [
                "id" => 477,
                "iata" => "L3",
                "icao" => "JOS",
                "name" => "DHL de Guatemala"
            ],
            [
                "id" => 478,
                "iata" => "L8",
                "icao" => "TON",
                "name" => "Lulutai Airlines"
            ],
            [
                "id" => 479,
                "iata" => "L9",
                "icao" => "LWI",
                "name" => "Lumiwings"
            ],
            [
                "id" => 480,
                "iata" => "LA",
                "icao" => "LAN",
                "name" => "LATAM Airlines"
            ],
            [
                "id" => 481,
                "iata" => "L7",
                "icao" => "LAE",
                "name" => "LATAM Cargo Colombia"
            ],
            [
                "id" => 482,
                "iata" => "4L",
                "icao" => "LAU",
                "name" => "LAS SA"
            ],
            [
                "id" => 483,
                "iata" => "AP",
                "icao" => "LAV",
                "name" => "AlbaStar"
            ],
            [
                "id" => 484,
                "iata" => "YQ",
                "icao" => "LCT",
                "name" => "TAR Aerolineas"
            ],
            [
                "id" => 485,
                "iata" => "LD",
                "icao" => "AHK",
                "name" => "AHK Air Hong Kong Limited"
            ],
            [
                "id" => 486,
                "iata" => "OE",
                "icao" => "LDM",
                "name" => "Lauda"
            ],
            [
                "id" => 487,
                "iata" => "LG",
                "icao" => "LGL",
                "name" => "Luxair"
            ],
            [
                "id" => 488,
                "iata" => "6T",
                "icao" => "LGT",
                "name" => "Longtail Aviation"
            ],
            [
                "id" => 489,
                "iata" => "LH",
                "icao" => "DLH",
                "name" => "Lufthansa"
            ],
            [
                "id" => 490,
                "iata" => "GI",
                "icao" => "LHA",
                "name" => "China Central Longhao"
            ],
            [
                "id" => 491,
                "iata" => "LI",
                "icao" => "EZZ",
                "name" => "ETF Airways"
            ],
            [
                "id" => 492,
                "iata" => "DU",
                "icao" => "LIZ",
                "name" => "Air Liaison"
            ],
            [
                "id" => 493,
                "iata" => "LJ",
                "icao" => "JNA",
                "name" => "Jin Air"
            ],
            [
                "id" => 494,
                "iata" => "LK",
                "icao" => "LLL",
                "name" => "Lao Skyway"
            ],
            [
                "id" => 495,
                "iata" => "LN",
                "icao" => "LAA",
                "name" => "Libyan Airlines"
            ],
            [
                "id" => 496,
                "iata" => "LO",
                "icao" => "LOT",
                "name" => "LOT - Polish Airlines"
            ],
            [
                "id" => 497,
                "iata" => "LM",
                "icao" => "LOG",
                "name" => "Loganair"
            ],
            [
                "id" => 498,
                "iata" => "LP",
                "icao" => "LPE",
                "name" => "LATAM Airlines Peru"
            ],
            [
                "id" => 499,
                "iata" => "FL",
                "icao" => "LPA",
                "name" => "Air Leap"
            ],
            [
                "id" => 500,
                "iata" => "LQ",
                "icao" => "MKR",
                "name" => "Lanmei Airlines"
            ],
            [
                "id" => 501,
                "iata" => "LR",
                "icao" => "LRC",
                "name" => "Avianca Costa Rica"
            ],
            [
                "id" => 502,
                "iata" => "LS",
                "icao" => "EXS",
                "name" => "Jet2"
            ],
            [
                "id" => 503,
                "iata" => "LU",
                "icao" => "LXP",
                "name" => "LATAM Airlines Chile"
            ],
            [
                "id" => 504,
                "iata" => "BN",
                "icao" => "LWG",
                "name" => "Luxwing"
            ],
            [
                "id" => 505,
                "iata" => "LX",
                "icao" => "SWR",
                "name" => "SWISS"
            ],
            [
                "id" => 506,
                "iata" => "LY",
                "icao" => "ELY",
                "name" => "EL AL"
            ],
            [
                "id" => 507,
                "iata" => "KG",
                "icao" => "LYM",
                "name" => "Denver Air Connection"
            ],
            [
                "id" => 508,
                "iata" => "M2",
                "icao" => "MHV",
                "name" => "MHS Aviation"
            ],
            [
                "id" => 509,
                "iata" => "M3",
                "icao" => "LTG",
                "name" => "LATAM Cargo Brasil"
            ],
            [
                "id" => 510,
                "iata" => "M5",
                "icao" => "KEN",
                "name" => "Kenmore Air"
            ],
            [
                "id" => 511,
                "iata" => "M6",
                "icao" => "AJT",
                "name" => "Amerijet International"
            ],
            [
                "id" => 512,
                "iata" => "M8",
                "icao" => "MSJ",
                "name" => "Skyjet"
            ],
            [
                "id" => 513,
                "iata" => "M9",
                "icao" => "MSI",
                "name" => "Motor Sich Airlines"
            ],
            [
                "id" => 514,
                "iata" => "NR",
                "icao" => "MAV",
                "name" => "Manta Aviation"
            ],
            [
                "id" => 515,
                "iata" => "MV",
                "icao" => "MAR",
                "name" => "Air Mediterranean"
            ],
            [
                "id" => 516,
                "iata" => "L6",
                "icao" => "MAI",
                "name" => "Mauritania Airlines"
            ],
            [
                "id" => 517,
                "iata" => "AL",
                "icao" => "MAY",
                "name" => "Malta Air"
            ],
            [
                "id" => 518,
                "iata" => "MB",
                "icao" => "MNB",
                "name" => "MNG Airlines"
            ],
            [
                "id" => 519,
                "iata" => "ZM",
                "icao" => "MBB",
                "name" => "Air Manas Air Company LLC"
            ],
            [
                "id" => 520,
                "iata" => "MC",
                "icao" => "RCH",
                "name" => "Air Mobility Command"
            ],
            [
                "id" => 521,
                "iata" => "T2",
                "icao" => "MCS",
                "name" => "MCS Aerocarga de Mexico"
            ],
            [
                "id" => 522,
                "iata" => "MD",
                "icao" => "MDG",
                "name" => "Air Madagascar"
            ],
            [
                "id" => 523,
                "iata" => "ME",
                "icao" => "MEA",
                "name" => "Middle East Airlines"
            ],
            [
                "id" => 524,
                "iata" => "MF",
                "icao" => "CXA",
                "name" => "Xiamen Airlines"
            ],
            [
                "id" => 525,
                "iata" => "XF",
                "icao" => "MGW",
                "name" => "Mongolian Airways"
            ],
            [
                "id" => 526,
                "iata" => "MH",
                "icao" => "MAS",
                "name" => "Malaysia Airlines"
            ],
            [
                "id" => 527,
                "iata" => "MI",
                "icao" => "SLK",
                "name" => "SilkAir"
            ],
            [
                "id" => 528,
                "iata" => "MJ",
                "icao" => "MYW",
                "name" => "Myway Airlines"
            ],
            [
                "id" => 529,
                "iata" => "MK",
                "icao" => "MAU",
                "name" => "Air Mauritius"
            ],
            [
                "id" => 530,
                "iata" => "DB",
                "icao" => "MLT",
                "name" => "Maleth-Aero"
            ],
            [
                "id" => 531,
                "iata" => "MR",
                "icao" => "MML",
                "name" => "Hunnu Air"
            ],
            [
                "id" => 532,
                "iata" => "MT",
                "icao" => "MTL",
                "name" => "RAF-Avia"
            ],
            [
                "id" => 533,
                "iata" => "X8",
                "icao" => "MMX",
                "name" => "Airmax"
            ],
            [
                "id" => 534,
                "iata" => "YU",
                "icao" => "MMZ",
                "name" => "EuroAtlantic Airways"
            ],
            [
                "id" => 535,
                "iata" => "MN",
                "icao" => "CAW",
                "name" => "Comair"
            ],
            [
                "id" => 536,
                "iata" => "M0",
                "icao" => "MNG",
                "name" => "Aero Mongolia"
            ],
            [
                "id" => 537,
                "iata" => "MO",
                "icao" => "CAV",
                "name" => "Calm Air International"
            ],
            [
                "id" => 538,
                "iata" => "MP",
                "icao" => "MPH",
                "name" => "Martinair"
            ],
            [
                "id" => 539,
                "iata" => "MQ",
                "icao" => "ENY",
                "name" => "Envoy Air"
            ],
            [
                "id" => 540,
                "iata" => "MS",
                "icao" => "MSR",
                "name" => "EgyptAir"
            ],
            [
                "id" => 541,
                "iata" => "M4",
                "icao" => "MSA",
                "name" => "Poste Air Cargo"
            ],
            [
                "id" => 542,
                "iata" => "SM",
                "icao" => "MSC",
                "name" => "Air Cairo"
            ],
            [
                "id" => 543,
                "iata" => "7N",
                "icao" => "MTD",
                "name" => "MetroJets"
            ],
            [
                "id" => 544,
                "iata" => "MU",
                "icao" => "CES",
                "name" => "China Eastern Airlines"
            ],
            [
                "id" => 545,
                "iata" => "WD",
                "icao" => "VVA",
                "name" => "Eleron"
            ],
            [
                "id" => 546,
                "iata" => "OD",
                "icao" => "MXD",
                "name" => "Malindo Air"
            ],
            [
                "id" => 547,
                "iata" => "6M",
                "icao" => "MXM",
                "name" => "Maximus Air"
            ],
            [
                "id" => 548,
                "iata" => "MX",
                "icao" => "MXY",
                "name" => "Breeze Airways"
            ],
            [
                "id" => 549,
                "iata" => "M7",
                "icao" => "MAA",
                "name" => "Masair"
            ],
            [
                "id" => 550,
                "iata" => "2M",
                "icao" => "MYD",
                "name" => "Maya Island Air"
            ],
            [
                "id" => 551,
                "iata" => "ND",
                "icao" => "NDA",
                "name" => "Nordica"
            ],
            [
                "id" => 552,
                "iata" => "N4",
                "icao" => "NWS",
                "name" => "Nordwind Airlines"
            ],
            [
                "id" => 553,
                "iata" => "N5",
                "icao" => "NRL",
                "name" => "Nolinor"
            ],
            [
                "id" => 554,
                "iata" => "N8",
                "icao" => "NCR",
                "name" => "National Airlines"
            ],
            [
                "id" => 555,
                "iata" => "NA",
                "icao" => "NSS",
                "name" => "Nesma Airlines"
            ],
            [
                "id" => 556,
                "iata" => "NO",
                "icao" => "NOS",
                "name" => "Neos"
            ],
            [
                "id" => 557,
                "iata" => "NC",
                "icao" => "NAC",
                "name" => "Northern Air Cargo"
            ],
            [
                "id" => 558,
                "iata" => "NE",
                "icao" => "NMA",
                "name" => "Nesma Airlines"
            ],
            [
                "id" => 559,
                "iata" => "NF",
                "icao" => "AVN",
                "name" => "Air Vanuatu"
            ],
            [
                "id" => 560,
                "iata" => "NG",
                "icao" => "NAI",
                "name" => "NOVAIR"
            ],
            [
                "id" => 561,
                "iata" => "RM",
                "icao" => "NGT",
                "name" => "Aircompany Armenia"
            ],
            [
                "id" => 562,
                "iata" => "NH",
                "icao" => "ANA",
                "name" => "ANA"
            ],
            [
                "id" => 563,
                "iata" => "NI",
                "icao" => "PGA",
                "name" => "Portugalia Airlines"
            ],
            [
                "id" => 564,
                "iata" => "6N",
                "icao" => "NIN",
                "name" => "Niger Airlines"
            ],
            [
                "id" => 565,
                "iata" => "NK",
                "icao" => "NKS",
                "name" => "Spirit Airlines"
            ],
            [
                "id" => 566,
                "iata" => "S5",
                "icao" => "NKP",
                "name" => "Abakan Air"
            ],
            [
                "id" => 567,
                "iata" => "NP",
                "icao" => "NIA",
                "name" => "Nile Air"
            ],
            [
                "id" => 568,
                "iata" => "NQ",
                "icao" => "AJX",
                "name" => "Air Japan"
            ],
            [
                "id" => 569,
                "iata" => "NS",
                "icao" => "HBH",
                "name" => "Hebei Airlines"
            ],
            [
                "id" => 570,
                "iata" => "NT",
                "icao" => "IBB",
                "name" => "Binter Canarias"
            ],
            [
                "id" => 571,
                "iata" => "3B",
                "icao" => "NTB",
                "name" => "Binter Cabo Verde"
            ],
            [
                "id" => 572,
                "iata" => "NU",
                "icao" => "JTA",
                "name" => "Japan Transocean Air"
            ],
            [
                "id" => 573,
                "iata" => "N9",
                "icao" => "SHA",
                "name" => "Shree Airlines"
            ],
            [
                "id" => 574,
                "iata" => "0E",
                "icao" => "NWC",
                "name" => "Aircompany North-West"
            ],
            [
                "id" => 575,
                "iata" => "NX",
                "icao" => "AMU",
                "name" => "Air Macau"
            ],
            [
                "id" => 576,
                "iata" => "NY",
                "icao" => "FXI",
                "name" => "Air Iceland Connect"
            ],
            [
                "id" => 577,
                "iata" => "OJ",
                "icao" => "NYX",
                "name" => "NyxAir"
            ],
            [
                "id" => 578,
                "iata" => "NZ",
                "icao" => "ANZ",
                "name" => "Air New Zealand"
            ],
            [
                "id" => 579,
                "iata" => "O2",
                "icao" => "HPK",
                "name" => "Linear Air"
            ],
            [
                "id" => 580,
                "iata" => "OP",
                "icao" => "DIG",
                "name" => "Passion Air"
            ],
            [
                "id" => 581,
                "iata" => "O3",
                "icao" => "CSS",
                "name" => "SF Airlines"
            ],
            [
                "id" => 582,
                "iata" => "O9",
                "icao" => "NOV",
                "name" => "NOVA Airways"
            ],
            [
                "id" => 583,
                "iata" => "OA",
                "icao" => "OAL",
                "name" => "Olympic Air"
            ],
            [
                "id" => 584,
                "iata" => "OY",
                "icao" => "ANS",
                "name" => "Andes Lineas Aereas"
            ],
            [
                "id" => 585,
                "iata" => "OB",
                "icao" => "BOV",
                "name" => "BoA"
            ],
            [
                "id" => 586,
                "iata" => "6O",
                "icao" => "OBS",
                "name" => "Orbest S.A."
            ],
            [
                "id" => 587,
                "iata" => "OC",
                "icao" => "ORC",
                "name" => "Oriental Air Bridge"
            ],
            [
                "id" => 588,
                "iata" => "OX",
                "icao" => "OEW",
                "name" => "One Airways"
            ],
            [
                "id" => 589,
                "iata" => "8Q",
                "icao" => "OHY",
                "name" => "Onur Air"
            ],
            [
                "id" => 590,
                "iata" => "OI",
                "icao" => "HND",
                "name" => "Hinterland Aviation"
            ],
            [
                "id" => 591,
                "iata" => "OF",
                "icao" => "OLA",
                "name" => "Overland Airways"
            ],
            [
                "id" => 592,
                "iata" => "OK",
                "icao" => "CSA",
                "name" => "CSA"
            ],
            [
                "id" => 593,
                "iata" => "OM",
                "icao" => "MGL",
                "name" => "Miat - Mongolian Airlines"
            ],
            [
                "id" => 594,
                "iata" => "O7",
                "icao" => "OMB",
                "name" => "Omni-Blu Aviation"
            ],
            [
                "id" => 595,
                "iata" => "OV",
                "icao" => "OMS",
                "name" => "SalamAir"
            ],
            [
                "id" => 596,
                "iata" => "ON",
                "icao" => "RON",
                "name" => "Nauru Airlines"
            ],
            [
                "id" => 597,
                "iata" => "OO",
                "icao" => "SKW",
                "name" => "SkyWest Airlines"
            ],
            [
                "id" => 598,
                "iata" => "OR",
                "icao" => "TFL",
                "name" => "TUIfly Netherlands"
            ],
            [
                "id" => 599,
                "iata" => "OS",
                "icao" => "AUA",
                "name" => "Austrian"
            ],
            [
                "id" => 600,
                "iata" => "OU",
                "icao" => "CTN",
                "name" => "Croatia Airlines"
            ],
            [
                "id" => 601,
                "iata" => "X5",
                "icao" => "OVA",
                "name" => "Air Europa Express"
            ],
            [
                "id" => 602,
                "iata" => "OW",
                "icao" => "SEW",
                "name" => "Skyward Express"
            ],
            [
                "id" => 603,
                "iata" => "2F",
                "icao" => "OWT",
                "name" => "Two Taxi Aereo"
            ],
            [
                "id" => 604,
                "iata" => "OZ",
                "icao" => "AAR",
                "name" => "Asiana Airlines"
            ],
            [
                "id" => 605,
                "iata" => "P0",
                "icao" => "PFZ",
                "name" => "Proflight Zambia"
            ],
            [
                "id" => 606,
                "iata" => "P2",
                "icao" => "XAK",
                "name" => "AirKenya Express Limited"
            ],
            [
                "id" => 607,
                "iata" => "2Z",
                "icao" => "PTB",
                "name" => "VoePass"
            ],
            [
                "id" => 608,
                "iata" => "P3",
                "icao" => "CLU",
                "name" => "Cargologicair"
            ],
            [
                "id" => 609,
                "iata" => "S0",
                "icao" => "NSO",
                "name" => "Aerolineas Sosa"
            ],
            [
                "id" => 610,
                "iata" => "P5",
                "icao" => "RPB",
                "name" => "Copa Airlines Colombia"
            ],
            [
                "id" => 611,
                "iata" => "P6",
                "icao" => "PVG",
                "name" => "Privilege Style"
            ],
            [
                "id" => 612,
                "iata" => "P9",
                "icao" => "MGE",
                "name" => "Asia Pacific Airlines"
            ],
            [
                "id" => 613,
                "iata" => "7M",
                "icao" => "PAM",
                "name" => "MAP Linhas Aereas"
            ],
            [
                "id" => 614,
                "iata" => "OL",
                "icao" => "PAO",
                "name" => "Samoa Airways"
            ],
            [
                "id" => 615,
                "iata" => "PB",
                "icao" => "PVL",
                "name" => "PAL Airlines"
            ],
            [
                "id" => 616,
                "iata" => "PC",
                "icao" => "PGT",
                "name" => "Pegasus"
            ],
            [
                "id" => 617,
                "iata" => "PD",
                "icao" => "POE",
                "name" => "Porter Airlines"
            ],
            [
                "id" => 618,
                "iata" => "PT",
                "icao" => "PDT",
                "name" => "Piedmont Airlines"
            ],
            [
                "id" => 619,
                "iata" => "PE",
                "icao" => "PEV",
                "name" => "Peoples"
            ],
            [
                "id" => 620,
                "iata" => "UF",
                "icao" => "PER",
                "name" => "Petroleum Air Services"
            ],
            [
                "id" => 621,
                "iata" => "PF",
                "icao" => "SIF",
                "name" => "Air Sial"
            ],
            [
                "id" => 622,
                "iata" => "PG",
                "icao" => "BKP",
                "name" => "Bangkok Airways"
            ],
            [
                "id" => 623,
                "iata" => "PH",
                "icao" => "PHA",
                "name" => "Phoenix Air Group"
            ],
            [
                "id" => 624,
                "iata" => "PJ",
                "icao" => "SPM",
                "name" => "Air Saint Pierre"
            ],
            [
                "id" => 625,
                "iata" => "PK",
                "icao" => "PIA",
                "name" => "Pakistan International Airlines"
            ],
            [
                "id" => 626,
                "iata" => "PL",
                "icao" => "SOA",
                "name" => "Southern Air Charter"
            ],
            [
                "id" => 627,
                "iata" => "PM",
                "icao" => "PSK",
                "name" => "Prescott Support Company"
            ],
            [
                "id" => 628,
                "iata" => "PN",
                "icao" => "CHB",
                "name" => "West Air (China)"
            ],
            [
                "id" => 629,
                "iata" => "PO",
                "icao" => "PAC",
                "name" => "Polar Air Cargo"
            ],
            [
                "id" => 630,
                "iata" => "PP",
                "icao" => "PJS",
                "name" => "Jet Aviation Business"
            ],
            [
                "id" => 631,
                "iata" => "PR",
                "icao" => "PAL",
                "name" => "Philippine Airlines"
            ],
            [
                "id" => 632,
                "iata" => "US",
                "icao" => "PRU",
                "name" => "Prinair"
            ],
            [
                "id" => 633,
                "iata" => "PS",
                "icao" => "AUI",
                "name" => "UIA"
            ],
            [
                "id" => 634,
                "iata" => "7P",
                "icao" => "PST",
                "name" => "Air Panama"
            ],
            [
                "id" => 635,
                "iata" => "PU",
                "icao" => "PUE",
                "name" => "Plus Ultra"
            ],
            [
                "id" => 636,
                "iata" => "PV",
                "icao" => "SBU",
                "name" => "Saint Barth Commuter"
            ],
            [
                "id" => 637,
                "iata" => "PW",
                "icao" => "PRF",
                "name" => "Precision Air"
            ],
            [
                "id" => 638,
                "iata" => "PX",
                "icao" => "ANG",
                "name" => "Air Niugini"
            ],
            [
                "id" => 639,
                "iata" => "PY",
                "icao" => "SLM",
                "name" => "Surinam Airways"
            ],
            [
                "id" => 640,
                "iata" => "PZ",
                "icao" => "LAP",
                "name" => "LATAM Airlines Paraguay"
            ],
            [
                "id" => 641,
                "iata" => "Q2",
                "icao" => "DQA",
                "name" => "Maldivian"
            ],
            [
                "id" => 642,
                "iata" => "Q4",
                "icao" => "ELE",
                "name" => "Euroairlines"
            ],
            [
                "id" => 643,
                "iata" => "Q5",
                "icao" => "MLA",
                "name" => "40-Mile Air"
            ],
            [
                "id" => 644,
                "iata" => "QN",
                "icao" => "SKP",
                "name" => "Skytrans"
            ],
            [
                "id" => 645,
                "iata" => "Q7",
                "icao" => "SBM",
                "name" => "SkyBahamas"
            ],
            [
                "id" => 646,
                "iata" => "Q8",
                "icao" => "TSG",
                "name" => "Trans Air Congo"
            ],
            [
                "id" => 647,
                "iata" => "QA",
                "icao" => "QBA",
                "name" => "Queen Bilqis Airways"
            ],
            [
                "id" => 648,
                "iata" => "VZ",
                "icao" => "TVJ",
                "name" => "Thai Vietjet Air"
            ],
            [
                "id" => 649,
                "iata" => "QE",
                "icao" => "EFA",
                "name" => "Express Freighters Australia"
            ],
            [
                "id" => 650,
                "iata" => "QF",
                "icao" => "QFA",
                "name" => "Qantas"
            ],
            [
                "id" => 651,
                "iata" => "GE",
                "icao" => "GBB",
                "name" => "Global Aviation"
            ],
            [
                "id" => 652,
                "iata" => "QH",
                "icao" => "BAV",
                "name" => "Bamboo Airways"
            ],
            [
                "id" => 653,
                "iata" => "QK",
                "icao" => "JZA",
                "name" => "Jazz"
            ],
            [
                "id" => 654,
                "iata" => "QL",
                "icao" => "LER",
                "name" => "LASER Airlines"
            ],
            [
                "id" => 655,
                "iata" => "HH",
                "icao" => "QNT",
                "name" => "Qanot Sharq"
            ],
            [
                "id" => 656,
                "iata" => "QQ",
                "icao" => "UTY",
                "name" => "Alliance Airlines"
            ],
            [
                "id" => 657,
                "iata" => "QR",
                "icao" => "QTR",
                "name" => "Qatar Airways"
            ],
            [
                "id" => 658,
                "iata" => "QS",
                "icao" => "TVS",
                "name" => "SmartWings"
            ],
            [
                "id" => 659,
                "iata" => "QT",
                "icao" => "TPA",
                "name" => "Avianca Cargo"
            ],
            [
                "id" => 660,
                "iata" => "QU",
                "icao" => "UTN",
                "name" => "Azur Air Ukraine"
            ],
            [
                "id" => 661,
                "iata" => "QV",
                "icao" => "LAO",
                "name" => "Lao Airlines"
            ],
            [
                "id" => 662,
                "iata" => "QW",
                "icao" => "QDA",
                "name" => "Qingdao Airlines"
            ],
            [
                "id" => 663,
                "iata" => "QX",
                "icao" => "QXE",
                "name" => "Horizon Air"
            ],
            [
                "id" => 664,
                "iata" => "QY",
                "icao" => "BCS",
                "name" => "EAT Leipzig"
            ],
            [
                "id" => 665,
                "iata" => "QZ",
                "icao" => "AWQ",
                "name" => "Indonesia AirAsia"
            ],
            [
                "id" => 666,
                "iata" => "R0",
                "icao" => "TTU",
                "name" => "Tantalus Air"
            ],
            [
                "id" => 667,
                "iata" => "RF",
                "icao" => "ERF",
                "name" => "Erofey"
            ],
            [
                "id" => 668,
                "iata" => "R3",
                "icao" => "SYL",
                "name" => "Yakutia"
            ],
            [
                "id" => 669,
                "iata" => "R6",
                "icao" => "DNU",
                "name" => "DOT LT"
            ],
            [
                "id" => 670,
                "iata" => "RA",
                "icao" => "RNA",
                "name" => "Nepal Airlines"
            ],
            [
                "id" => 671,
                "iata" => "RB",
                "icao" => "SYR",
                "name" => "Syrian Air"
            ],
            [
                "id" => 672,
                "iata" => "E5",
                "icao" => "RBG",
                "name" => "Air Arabia Egypt"
            ],
            [
                "id" => 673,
                "iata" => "RC",
                "icao" => "FLI",
                "name" => "Atlantic Airways"
            ],
            [
                "id" => 674,
                "iata" => "RD",
                "icao" => "SNA",
                "name" => "Sky Cana"
            ],
            [
                "id" => 675,
                "iata" => "RE",
                "icao" => "STK",
                "name" => "Stobart Air"
            ],
            [
                "id" => 676,
                "iata" => "8N",
                "icao" => "REG",
                "name" => "Regional Air"
            ],
            [
                "id" => 677,
                "iata" => "RH",
                "icao" => "HKC",
                "name" => "Hong Kong Air Cargo Carrier"
            ],
            [
                "id" => 678,
                "iata" => "GP",
                "icao" => "RIV",
                "name" => "APG Airlines"
            ],
            [
                "id" => 679,
                "iata" => "RJ",
                "icao" => "RJA",
                "name" => "Royal Jordanian"
            ],
            [
                "id" => 680,
                "iata" => "RG",
                "icao" => "RJD",
                "name" => "Rotana Jet"
            ],
            [
                "id" => 681,
                "iata" => "RK",
                "icao" => "RUK",
                "name" => "Ryanair UK"
            ],
            [
                "id" => 682,
                "iata" => "PI",
                "icao" => "RKA",
                "name" => "Polar Airlines"
            ],
            [
                "id" => 683,
                "iata" => "RO",
                "icao" => "ROT",
                "name" => "TAROM"
            ],
            [
                "id" => 684,
                "iata" => "RQ",
                "icao" => "KMF",
                "name" => "Kam Air"
            ],
            [
                "id" => 685,
                "iata" => "RR",
                "icao" => "RYS",
                "name" => "Ryanair Sun"
            ],
            [
                "id" => 686,
                "iata" => "KV",
                "icao" => "SKV",
                "name" => "Sky Regional"
            ],
            [
                "id" => 687,
                "iata" => "RS",
                "icao" => "ASV",
                "name" => "Air Seoul"
            ],
            [
                "id" => 688,
                "iata" => "F7",
                "icao" => "RSY",
                "name" => "LTD I Fly"
            ],
            [
                "id" => 689,
                "iata" => "7T",
                "icao" => "RTM",
                "name" => "Trans AM"
            ],
            [
                "id" => 690,
                "iata" => "RU",
                "icao" => "ABW",
                "name" => "AirBridgeCargo"
            ],
            [
                "id" => 691,
                "iata" => "5R",
                "icao" => "RUC",
                "name" => "Rutaca Airlines"
            ],
            [
                "id" => 692,
                "iata" => "9T",
                "icao" => "RUN",
                "name" => "myCargo Airlines"
            ],
            [
                "id" => 693,
                "iata" => "RV",
                "icao" => "ROU",
                "name" => "Air Canada Rouge"
            ],
            [
                "id" => 694,
                "iata" => "RW",
                "icao" => "RYL",
                "name" => "Royal Air"
            ],
            [
                "id" => 695,
                "iata" => "RY",
                "icao" => "CJX",
                "name" => "Jiangxi Airlines"
            ],
            [
                "id" => 696,
                "iata" => "RZ",
                "icao" => "LRS",
                "name" => "SANSA Regional"
            ],
            [
                "id" => 697,
                "iata" => "S4",
                "icao" => "RZO",
                "name" => "Azores Airlines"
            ],
            [
                "id" => 698,
                "iata" => "S6",
                "icao" => "KSZ",
                "name" => "Sunrise Airways"
            ],
            [
                "id" => 699,
                "iata" => "S7",
                "icao" => "SBI",
                "name" => "S7 Airlines"
            ],
            [
                "id" => 700,
                "iata" => "SB",
                "icao" => "ACI",
                "name" => "Aircalin"
            ],
            [
                "id" => 701,
                "iata" => "SC",
                "icao" => "CDG",
                "name" => "Shandong Airlines"
            ],
            [
                "id" => 702,
                "iata" => "SD",
                "icao" => "SUD",
                "name" => "Sudan Airways"
            ],
            [
                "id" => 703,
                "iata" => "SR",
                "icao" => "SDR",
                "name" => "SundAir"
            ],
            [
                "id" => 704,
                "iata" => "4R",
                "icao" => "SEK",
                "name" => "Star East Airline"
            ],
            [
                "id" => 705,
                "iata" => "ER",
                "icao" => "SEP",
                "name" => "Serene Air"
            ],
            [
                "id" => 706,
                "iata" => "SF",
                "icao" => "DTH",
                "name" => "Tassili Airlines"
            ],
            [
                "id" => 707,
                "iata" => "SG",
                "icao" => "SEJ",
                "name" => "SpiceJet"
            ],
            [
                "id" => 708,
                "iata" => "DO",
                "icao" => "SHH",
                "name" => "Sky High Aviation"
            ],
            [
                "id" => 709,
                "iata" => "IS",
                "icao" => "SHI",
                "name" => "Sepehran Airlines"
            ],
            [
                "id" => 710,
                "iata" => "SI",
                "icao" => "SPA",
                "name" => "Sierra Pacific Airlines"
            ],
            [
                "id" => 711,
                "iata" => "SJ",
                "icao" => "SJY",
                "name" => "Sriwijaya Air"
            ],
            [
                "id" => 712,
                "iata" => "IJ",
                "icao" => "SJO",
                "name" => "Spring Airlines Japan"
            ],
            [
                "id" => 713,
                "iata" => "IU",
                "icao" => "SJV",
                "name" => "Super Air Jet"
            ],
            [
                "id" => 714,
                "iata" => "SK",
                "icao" => "SAS",
                "name" => "SAS"
            ],
            [
                "id" => 715,
                "iata" => "SL",
                "icao" => "SZS",
                "name" => "SAS Ireland"
            ],
            [
                "id" => 716,
                "iata" => "SZ",
                "icao" => "SMR",
                "name" => "Somon Air"
            ],
            [
                "id" => 717,
                "iata" => "SN",
                "icao" => "BEL",
                "name" => "Brussels Airlines"
            ],
            [
                "id" => 718,
                "iata" => "2Q",
                "icao" => "SNC",
                "name" => "Air Cargo Carriers"
            ],
            [
                "id" => 719,
                "iata" => "6J",
                "icao" => "SNJ",
                "name" => "Solaseed Air"
            ],
            [
                "id" => 720,
                "iata" => "9S",
                "icao" => "SOO",
                "name" => "Southern Air"
            ],
            [
                "id" => 721,
                "iata" => "SP",
                "icao" => "SAT",
                "name" => "SATA Air Acores"
            ],
            [
                "id" => 722,
                "iata" => "SQ",
                "icao" => "SIA",
                "name" => "Singapore Airlines"
            ],
            [
                "id" => 723,
                "iata" => "PQ",
                "icao" => "SQP",
                "name" => "SkyUP Airlines"
            ],
            [
                "id" => 724,
                "iata" => "P8",
                "icao" => "SRN",
                "name" => "Sprint Air"
            ],
            [
                "id" => 725,
                "iata" => "DJ",
                "icao" => "SRR",
                "name" => "Star Air A/S"
            ],
            [
                "id" => 726,
                "iata" => "IH",
                "icao" => "SRS",
                "name" => "Southern Sky"
            ],
            [
                "id" => 727,
                "iata" => "VC",
                "icao" => "SRY",
                "name" => "ViaAir"
            ],
            [
                "id" => 728,
                "iata" => "SS",
                "icao" => "CRL",
                "name" => "Corsair"
            ],
            [
                "id" => 729,
                "iata" => "ST",
                "icao" => "RTL",
                "name" => "Air Thanlwin"
            ],
            [
                "id" => 730,
                "iata" => "SU",
                "icao" => "AFL",
                "name" => "Aeroflot"
            ],
            [
                "id" => 731,
                "iata" => "0A",
                "icao" => "SUL",
                "name" => "Asta Linhas"
            ],
            [
                "id" => 732,
                "iata" => "SV",
                "icao" => "SVA",
                "name" => "Saudia"
            ],
            [
                "id" => 733,
                "iata" => "WG",
                "icao" => "SWG",
                "name" => "Sunwing"
            ],
            [
                "id" => 734,
                "iata" => "WT",
                "icao" => "SWT",
                "name" => "Swiftair"
            ],
            [
                "id" => 735,
                "iata" => "SY",
                "icao" => "SCX",
                "name" => "Sun Country Airlines"
            ],
            [
                "id" => 736,
                "iata" => "SO",
                "icao" => "SYA",
                "name" => "Syphax Airlines"
            ],
            [
                "id" => 737,
                "iata" => "HC",
                "icao" => "SZN",
                "name" => "Air Senegal"
            ],
            [
                "id" => 738,
                "iata" => "T3",
                "icao" => "EZE",
                "name" => "Eastern Airways"
            ],
            [
                "id" => 739,
                "iata" => "T4",
                "icao" => "RDS",
                "name" => "Rhoades Aviation"
            ],
            [
                "id" => 740,
                "iata" => "T5",
                "icao" => "TUA",
                "name" => "Turkmenistan Airlines"
            ],
            [
                "id" => 741,
                "iata" => "T7",
                "icao" => "TIW",
                "name" => "Transcarga"
            ],
            [
                "id" => 742,
                "iata" => "T8",
                "icao" => "TVR",
                "name" => "Terra Avia"
            ],
            [
                "id" => 743,
                "iata" => "TA",
                "icao" => "TAK",
                "name" => "Transafrican Air"
            ],
            [
                "id" => 744,
                "iata" => "B4",
                "icao" => "TAN",
                "name" => "ZanAir"
            ],
            [
                "id" => 745,
                "iata" => "TC",
                "icao" => "ATC",
                "name" => "Air Tanzania"
            ],
            [
                "id" => 746,
                "iata" => "TE",
                "icao" => "IGA",
                "name" => "Sky Taxi"
            ],
            [
                "id" => 747,
                "iata" => "TG",
                "icao" => "THA",
                "name" => "Thai Airways International"
            ],
            [
                "id" => 748,
                "iata" => "IL",
                "icao" => "TGN",
                "name" => "Trigana Air"
            ],
            [
                "id" => 749,
                "iata" => "5U",
                "icao" => "TGU",
                "name" => "TAG"
            ],
            [
                "id" => 750,
                "iata" => "TH",
                "icao" => "RMY",
                "name" => "Raya Airways"
            ],
            [
                "id" => 751,
                "iata" => "TI",
                "icao" => "TWI",
                "name" => "Tailwind"
            ],
            [
                "id" => 752,
                "iata" => "5L",
                "icao" => "TIC",
                "name" => "TAC"
            ],
            [
                "id" => 753,
                "iata" => "TJ",
                "icao" => "GPD",
                "name" => "Tradewind Aviation"
            ],
            [
                "id" => 754,
                "iata" => "E8",
                "icao" => "TJB",
                "name" => "Tayaran Jet"
            ],
            [
                "id" => 755,
                "iata" => "TK",
                "icao" => "THY",
                "name" => "Turkish Airlines"
            ],
            [
                "id" => 756,
                "iata" => "TL",
                "icao" => "ANO",
                "name" => "Airnorth"
            ],
            [
                "id" => 757,
                "iata" => "TM",
                "icao" => "LAM",
                "name" => "LAM"
            ],
            [
                "id" => 758,
                "iata" => "TN",
                "icao" => "THT",
                "name" => "Air Tahiti Nui"
            ],
            [
                "id" => 759,
                "iata" => "8B",
                "icao" => "TNU",
                "name" => "TransNusa"
            ],
            [
                "id" => 760,
                "iata" => "TO",
                "icao" => "TVF",
                "name" => "Transavia France"
            ],
            [
                "id" => 761,
                "iata" => "BY",
                "icao" => "TOM",
                "name" => "TUI Airways"
            ],
            [
                "id" => 762,
                "iata" => "SX",
                "icao" => "TOR",
                "name" => "FlyGTA Airlines"
            ],
            [
                "id" => 763,
                "iata" => "9N",
                "icao" => "TOS",
                "name" => "Tropic Air"
            ],
            [
                "id" => 764,
                "iata" => "9L",
                "icao" => "TOW",
                "name" => "AirTanker Services"
            ],
            [
                "id" => 765,
                "iata" => "TP",
                "icao" => "TAP",
                "name" => "TAP Air Portugal"
            ],
            [
                "id" => 766,
                "iata" => "TR",
                "icao" => "TGW",
                "name" => "Scoot"
            ],
            [
                "id" => 767,
                "iata" => "2T",
                "icao" => "TRJ",
                "name" => "TruJet"
            ],
            [
                "id" => 768,
                "iata" => "3T",
                "icao" => "TRQ",
                "name" => "Tarco Aviation"
            ],
            [
                "id" => 769,
                "iata" => "TS",
                "icao" => "TSC",
                "name" => "Air Transat"
            ],
            [
                "id" => 770,
                "iata" => "TT",
                "icao" => "TGG",
                "name" => "Tigerair Australia"
            ],
            [
                "id" => 771,
                "iata" => "IT",
                "icao" => "TTW",
                "name" => "Tigerair Taiwan"
            ],
            [
                "id" => 772,
                "iata" => "TU",
                "icao" => "TAR",
                "name" => "Tunisair"
            ],
            [
                "id" => 773,
                "iata" => "TV",
                "icao" => "TBA",
                "name" => "Tibet Airlines"
            ],
            [
                "id" => 774,
                "iata" => "3Z",
                "icao" => "TVP",
                "name" => "Smartwings Poland"
            ],
            [
                "id" => 775,
                "iata" => "6D",
                "icao" => "TVQ",
                "name" => "Travel Service Slovensko"
            ],
            [
                "id" => 776,
                "iata" => "TW",
                "icao" => "TWB",
                "name" => "T'Way Air"
            ],
            [
                "id" => 777,
                "iata" => "V2",
                "icao" => "TWN",
                "name" => "Avialeasing"
            ],
            [
                "id" => 778,
                "iata" => "TX",
                "icao" => "FWI",
                "name" => "Air Caraibes"
            ],
            [
                "id" => 779,
                "iata" => "TY",
                "icao" => "TPC",
                "name" => "Air Caledonie"
            ],
            [
                "id" => 780,
                "iata" => "Y7",
                "icao" => "TYA",
                "name" => "NordStar Airlines"
            ],
            [
                "id" => 781,
                "iata" => "TZ",
                "icao" => "TDS",
                "name" => "Tsaradia"
            ],
            [
                "id" => 782,
                "iata" => "N6",
                "icao" => "TZS",
                "name" => "Aircompany TCA"
            ],
            [
                "id" => 783,
                "iata" => "U2",
                "icao" => "EZY",
                "name" => "easyJet"
            ],
            [
                "id" => 784,
                "iata" => "UY",
                "icao" => "SPD",
                "name" => "Sky Prime Charter"
            ],
            [
                "id" => 785,
                "iata" => "U3",
                "icao" => "SAY",
                "name" => "Sky Gates Airlines"
            ],
            [
                "id" => 786,
                "iata" => "U6",
                "icao" => "SVR",
                "name" => "Ural Airlines"
            ],
            [
                "id" => 787,
                "iata" => "U7",
                "icao" => "UCG",
                "name" => "Uniworld Air Cargo"
            ],
            [
                "id" => 788,
                "iata" => "UA",
                "icao" => "UAL",
                "name" => "United Airlines"
            ],
            [
                "id" => 789,
                "iata" => "UB",
                "icao" => "UBA",
                "name" => "Myanmar National Airlines"
            ],
            [
                "id" => 790,
                "iata" => "UD",
                "icao" => "UBD",
                "name" => "UBD"
            ],
            [
                "id" => 791,
                "iata" => "7B",
                "icao" => "UBE",
                "name" => "Bees Airline"
            ],
            [
                "id" => 792,
                "iata" => "UC",
                "icao" => "LCO",
                "name" => "LATAM Cargo Chile"
            ],
            [
                "id" => 793,
                "iata" => "UG",
                "icao" => "TUX",
                "name" => "Tunisair Express"
            ],
            [
                "id" => 794,
                "iata" => "UI",
                "icao" => "AUK",
                "name" => "Auric Air"
            ],
            [
                "id" => 795,
                "iata" => "UE",
                "icao" => "UJC",
                "name" => "Ultimate Air Shuttle"
            ],
            [
                "id" => 796,
                "iata" => "UK",
                "icao" => "VTI",
                "name" => "Vistara"
            ],
            [
                "id" => 797,
                "iata" => "UL",
                "icao" => "ALK",
                "name" => "SriLankan Airlines"
            ],
            [
                "id" => 798,
                "iata" => "UM",
                "icao" => "AZW",
                "name" => "Air Zimbabwe"
            ],
            [
                "id" => 799,
                "iata" => "UO",
                "icao" => "HKE",
                "name" => "Hong Kong Express"
            ],
            [
                "id" => 800,
                "iata" => "UP",
                "icao" => "BHS",
                "name" => "Bahamasair"
            ],
            [
                "id" => 801,
                "iata" => "UQ",
                "icao" => "CUH",
                "name" => "Urumqi Airlines"
            ],
            [
                "id" => 802,
                "iata" => "UR",
                "icao" => "UGD",
                "name" => "Uganda Airlines"
            ],
            [
                "id" => 803,
                "iata" => "UT",
                "icao" => "UTA",
                "name" => "UTair Aviation"
            ],
            [
                "id" => 804,
                "iata" => "UU",
                "icao" => "REU",
                "name" => "Air Austral"
            ],
            [
                "id" => 805,
                "iata" => "UV",
                "icao" => "UVA",
                "name" => "Universal Airways"
            ],
            [
                "id" => 806,
                "iata" => "RT",
                "icao" => "UVT",
                "name" => "UVT Aero"
            ],
            [
                "id" => 807,
                "iata" => "UW",
                "icao" => "UTP",
                "name" => "Uni-Top Airlines"
            ],
            [
                "id" => 808,
                "iata" => "UX",
                "icao" => "AEA",
                "name" => "Air Europa"
            ],
            [
                "id" => 809,
                "iata" => "UZ",
                "icao" => "BRQ",
                "name" => "Buraq Air"
            ],
            [
                "id" => 810,
                "iata" => "V0",
                "icao" => "VCV",
                "name" => "Conviasa"
            ],
            [
                "id" => 811,
                "iata" => "VU",
                "icao" => "VAG",
                "name" => "Vietravel Airlines"
            ],
            [
                "id" => 812,
                "iata" => "V3",
                "icao" => "KRP",
                "name" => "Carpatair"
            ],
            [
                "id" => 813,
                "iata" => "V4",
                "icao" => "VES",
                "name" => "Vieques Air Link"
            ],
            [
                "id" => 814,
                "iata" => "V8",
                "icao" => "IAR",
                "name" => "Iliamna Air Taxi"
            ],
            [
                "id" => 815,
                "iata" => "V9",
                "icao" => "VAA",
                "name" => "Citywing"
            ],
            [
                "id" => 816,
                "iata" => "VA",
                "icao" => "VOZ",
                "name" => "Virgin Australia"
            ],
            [
                "id" => 817,
                "iata" => "F6",
                "icao" => "VAW",
                "name" => "Fly2Sky"
            ],
            [
                "id" => 818,
                "iata" => "VB",
                "icao" => "VIV",
                "name" => "VivaAerobus"
            ],
            [
                "id" => 819,
                "iata" => "VI",
                "icao" => "VDA",
                "name" => "Volga-Dnepr"
            ],
            [
                "id" => 820,
                "iata" => "V6",
                "icao" => "VIL",
                "name" => "VI Airlink"
            ],
            [
                "id" => 821,
                "iata" => "VJ",
                "icao" => "VJC",
                "name" => "VietJet Air"
            ],
            [
                "id" => 822,
                "iata" => "VM",
                "icao" => "NGL",
                "name" => "Max Air"
            ],
            [
                "id" => 823,
                "iata" => "VN",
                "icao" => "HVN",
                "name" => "Vietnam Airlines"
            ],
            [
                "id" => 824,
                "iata" => "Q6",
                "icao" => "VOC",
                "name" => "Volaris Costa Rica"
            ],
            [
                "id" => 825,
                "iata" => "V7",
                "icao" => "VOE",
                "name" => "Volotea"
            ],
            [
                "id" => 826,
                "iata" => "N3",
                "icao" => "VOS",
                "name" => "Vuela El Salvador"
            ],
            [
                "id" => 827,
                "iata" => "VP",
                "icao" => "VQI",
                "name" => "Flyme"
            ],
            [
                "id" => 828,
                "iata" => "VV",
                "icao" => "VPE",
                "name" => "Viva Airlines Peru"
            ],
            [
                "id" => 829,
                "iata" => "VQ",
                "icao" => "NVQ",
                "name" => "Novoair"
            ],
            [
                "id" => 830,
                "iata" => "VR",
                "icao" => "TCV",
                "name" => "TACV Cabo Verde Airlines"
            ],
            [
                "id" => 831,
                "iata" => "HF",
                "icao" => "VRE",
                "name" => "Air Cote D'Ivoire"
            ],
            [
                "id" => 832,
                "iata" => "VO",
                "icao" => "VRG",
                "name" => "Voyage Air"
            ],
            [
                "id" => 833,
                "iata" => "VS",
                "icao" => "VIR",
                "name" => "Virgin Atlantic"
            ],
            [
                "id" => 834,
                "iata" => "VT",
                "icao" => "VTA",
                "name" => "Air Tahiti"
            ],
            [
                "id" => 835,
                "iata" => "LF",
                "icao" => "VTE",
                "name" => "Contour Aviation"
            ],
            [
                "id" => 836,
                "iata" => "T9",
                "icao" => "VTU",
                "name" => "Turpial Airlines"
            ],
            [
                "id" => 837,
                "iata" => "VH",
                "icao" => "VVC",
                "name" => "Viva Air Colombia"
            ],
            [
                "id" => 838,
                "iata" => "VW",
                "icao" => "TAO",
                "name" => "Aeromar"
            ],
            [
                "id" => 839,
                "iata" => "VY",
                "icao" => "VLG",
                "name" => "Vueling"
            ],
            [
                "id" => 840,
                "iata" => "W2",
                "icao" => "FXT",
                "name" => "FlexFlight"
            ],
            [
                "id" => 841,
                "iata" => "WW",
                "icao" => "KXP",
                "name" => "Kargo Xpress"
            ],
            [
                "id" => 842,
                "iata" => "W3",
                "icao" => "ARA",
                "name" => "Arik Air Limited"
            ],
            [
                "id" => 843,
                "iata" => "W5",
                "icao" => "IRM",
                "name" => "Mahan Air"
            ],
            [
                "id" => 844,
                "iata" => "W6",
                "icao" => "WZZ",
                "name" => "Wizz Air"
            ],
            [
                "id" => 845,
                "iata" => "W8",
                "icao" => "CJT",
                "name" => "Cargojet"
            ],
            [
                "id" => 846,
                "iata" => "W9",
                "icao" => "WUK",
                "name" => "Wizz Air UK"
            ],
            [
                "id" => 847,
                "iata" => "WA",
                "icao" => "KLC",
                "name" => "KLM Cityhopper"
            ],
            [
                "id" => 848,
                "iata" => "WV",
                "icao" => "WAA",
                "name" => "FlyWestair"
            ],
            [
                "id" => 849,
                "iata" => "F4",
                "icao" => "WAF",
                "name" => "Air Flamenco"
            ],
            [
                "id" => 850,
                "iata" => "5W",
                "icao" => "WAZ",
                "name" => "Wizz Air Abu Dhabi"
            ],
            [
                "id" => 851,
                "iata" => "WB",
                "icao" => "RWD",
                "name" => "RwandAir"
            ],
            [
                "id" => 852,
                "iata" => "WC",
                "icao" => "QHD",
                "name" => "Meregrass Inc"
            ],
            [
                "id" => 853,
                "iata" => "ZQ",
                "icao" => "GER",
                "name" => "WDL Aviation"
            ],
            [
                "id" => 854,
                "iata" => "WE",
                "icao" => "THD",
                "name" => "Thai Smile"
            ],
            [
                "id" => 855,
                "iata" => "WR",
                "icao" => "WEN",
                "name" => "WestJet Encore"
            ],
            [
                "id" => 856,
                "iata" => "4T",
                "icao" => "WEW",
                "name" => "Rise Air"
            ],
            [
                "id" => 857,
                "iata" => "WF",
                "icao" => "WIF",
                "name" => "Wideroe"
            ],
            [
                "id" => 858,
                "iata" => "2W",
                "icao" => "WFL",
                "name" => "World 2 Fly"
            ],
            [
                "id" => 859,
                "iata" => "WI",
                "icao" => "WHT",
                "name" => "White Airways"
            ],
            [
                "id" => 860,
                "iata" => "WJ",
                "icao" => "JES",
                "name" => "Jetsmart Airlines"
            ],
            [
                "id" => 861,
                "iata" => "WM",
                "icao" => "WIA",
                "name" => "Winair"
            ],
            [
                "id" => 862,
                "iata" => "MW",
                "icao" => "WZM",
                "name" => "Waltzing Matilda Aviation"
            ],
            [
                "id" => 863,
                "iata" => "WN",
                "icao" => "SWA",
                "name" => "Southwest Airlines"
            ],
            [
                "id" => 864,
                "iata" => "WS",
                "icao" => "WJA",
                "name" => "WestJet"
            ],
            [
                "id" => 865,
                "iata" => "WU",
                "icao" => "WST",
                "name" => "Western Air"
            ],
            [
                "id" => 866,
                "iata" => "WO",
                "icao" => "WSW",
                "name" => "Swoop"
            ],
            [
                "id" => 867,
                "iata" => "YJ",
                "icao" => "WUA",
                "name" => "Asian Express General Aviation Wuxi"
            ],
            [
                "id" => 868,
                "iata" => "WX",
                "icao" => "BCY",
                "name" => "Cityjet"
            ],
            [
                "id" => 869,
                "iata" => "WY",
                "icao" => "OMA",
                "name" => "Oman Air"
            ],
            [
                "id" => 870,
                "iata" => "WZ",
                "icao" => "RWZ",
                "name" => "Red Wings"
            ],
            [
                "id" => 871,
                "iata" => "X3",
                "icao" => "TUI",
                "name" => "TUIfly"
            ],
            [
                "id" => 872,
                "iata" => "X9",
                "icao" => "NVD",
                "name" => "JSC Avion Express"
            ],
            [
                "id" => 873,
                "iata" => "XA",
                "icao" => "XAA",
                "name" => "ARINC"
            ],
            [
                "id" => 874,
                "iata" => "A8",
                "icao" => "XAU",
                "name" => "Aerolink Uganda"
            ],
            [
                "id" => 875,
                "iata" => "XE",
                "icao" => "JSX",
                "name" => "JSX Air"
            ],
            [
                "id" => 876,
                "iata" => "XJ",
                "icao" => "TAX",
                "name" => "Thai AirAsia X"
            ],
            [
                "id" => 877,
                "iata" => "XK",
                "icao" => "CCM",
                "name" => "Air Corsica"
            ],
            [
                "id" => 878,
                "iata" => "XL",
                "icao" => "LNE",
                "name" => "LATAM Airlines Ecuador"
            ],
            [
                "id" => 879,
                "iata" => "XO",
                "icao" => "SGD",
                "name" => "South East Asian Airlines (SEAIR)"
            ],
            [
                "id" => 880,
                "iata" => "XP",
                "icao" => "CXP",
                "name" => "Avelo Airlines"
            ],
            [
                "id" => 881,
                "iata" => "XQ",
                "icao" => "SXS",
                "name" => "SunExpress"
            ],
            [
                "id" => 882,
                "iata" => "7A",
                "icao" => "XRC",
                "name" => "Express Air"
            ],
            [
                "id" => 883,
                "iata" => "XU",
                "icao" => "AXK",
                "name" => "African Express Airways"
            ],
            [
                "id" => 884,
                "iata" => "XY",
                "icao" => "KNE",
                "name" => "flynas"
            ],
            [
                "id" => 885,
                "iata" => "Y4",
                "icao" => "VOI",
                "name" => "Volaris"
            ],
            [
                "id" => 886,
                "iata" => "Y5",
                "icao" => "GMR",
                "name" => "Golden Myanmar Airlines Public Co. Ltd"
            ],
            [
                "id" => 887,
                "iata" => "Y8",
                "icao" => "YZR",
                "name" => "Suparna Airlines"
            ],
            [
                "id" => 888,
                "iata" => "YC",
                "icao" => "LLM",
                "name" => "Yamal Airlines"
            ],
            [
                "id" => 889,
                "iata" => "YD",
                "icao" => "SYG",
                "name" => "Synergy Aviation"
            ],
            [
                "id" => 890,
                "iata" => "YG",
                "icao" => "HYT",
                "name" => "YTO Cargo"
            ],
            [
                "id" => 891,
                "iata" => "YL",
                "icao" => "LWA",
                "name" => "Libyan Wings"
            ],
            [
                "id" => 892,
                "iata" => "YM",
                "icao" => "MGX",
                "name" => "Montenegro Airlines"
            ],
            [
                "id" => 893,
                "iata" => "YN",
                "icao" => "CRQ",
                "name" => "Air Creebec"
            ],
            [
                "id" => 894,
                "iata" => "YO",
                "icao" => "MCM",
                "name" => "Heli Air Monaco"
            ],
            [
                "id" => 895,
                "iata" => "YR",
                "icao" => "SCE",
                "name" => "Scenic Airlines"
            ],
            [
                "id" => 896,
                "iata" => "YT",
                "icao" => "NYT",
                "name" => "Yeti Airlines"
            ],
            [
                "id" => 897,
                "iata" => "YV",
                "icao" => "ASH",
                "name" => "Mesa Airlines"
            ],
            [
                "id" => 898,
                "iata" => "YW",
                "icao" => "ANE",
                "name" => "Air Nostrum"
            ],
            [
                "id" => 899,
                "iata" => "YX",
                "icao" => "RPA",
                "name" => "Republic Airways"
            ],
            [
                "id" => 900,
                "iata" => "ZU",
                "icao" => "EMZ",
                "name" => "Ethiopian Mozambique Airlines"
            ],
            [
                "id" => 901,
                "iata" => "Z2",
                "icao" => "APG",
                "name" => "Philippines AirAsia"
            ],
            [
                "id" => 902,
                "iata" => "YK",
                "icao" => "AVJ",
                "name" => "Avia Traffic Company LLC"
            ],
            [
                "id" => 903,
                "iata" => "Z7",
                "icao" => "AUZ",
                "name" => "Cristalux SA"
            ],
            [
                "id" => 904,
                "iata" => "Z8",
                "icao" => "AZN",
                "name" => "Amaszonas SA"
            ],
            [
                "id" => 905,
                "iata" => "ZA",
                "icao" => "SWM",
                "name" => "Sky Angkor Airlines Co., Ltd"
            ],
            [
                "id" => 906,
                "iata" => "ZB",
                "icao" => "ABN",
                "name" => "Air Albania"
            ],
            [
                "id" => 907,
                "iata" => "ZG",
                "icao" => "TZP",
                "name" => "ZIPAIR Tokyo"
            ],
            [
                "id" => 908,
                "iata" => "ZH",
                "icao" => "CSZ",
                "name" => "Shenzhen Airlines"
            ],
            [
                "id" => 909,
                "iata" => "ZK",
                "icao" => "ZAV",
                "name" => "Aircompany ZetAvia"
            ],
            [
                "id" => 910,
                "iata" => "ZL",
                "icao" => "RXA",
                "name" => "Rex Regional Express"
            ],
            [
                "id" => 911,
                "iata" => "WL",
                "icao" => "WAL",
                "name" => "World Atlantic Airlines"
            ],
            [
                "id" => 912,
                "iata" => "ZR",
                "icao" => "AZS",
                "name" => "Aviacon Zitotrans"
            ],
            [
                "id" => 913,
                "iata" => "ZW",
                "icao" => "AWI",
                "name" => "Air Wisconsin"
            ],
            [
                "id" => 914,
                "iata" => "ZX",
                "icao" => "GGN",
                "name" => "Air Georgian"
            ],
            [
                "id" => 915,
                "iata" => "MG",
                "icao" => "EZA",
                "name" => "Eznis Airways"
            ]
        ];
    }
}
