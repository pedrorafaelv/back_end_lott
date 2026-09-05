<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ficha;
use Illuminate\Support\Facades\DB;  

class FichaSeeder extends Seeder
{
    /** 
     * Run the database seeds.
     *
     * @return void
     */

     /*public $iconos = array('faCoffee', 'faGuitar', 'faAnchor','faAmbulance', 'faAppleAlt', 'faBed', 'faBell','faBeer', 'faBible', 
    'faBicycle', 'faBirthdayCake', 'faBook','faBolt', 'faBone', 'faBowlingBall', 'faBoxOpen',  'faBug', 'faBrush', 'faBuilding', 'faBusAlt',
    'faCalculator', 'faCalendar', 'faCamera',  'faCapsules', 'faCar', 'faCarBattery', 'faCarrot', 'faCat', 'faChair', 'faCheese', 'faChess',
    'faChurch', 'faCity', 'faCheck', 'faCheckCircle', 'faCheckDouble', 'faCheckDouble', 'faClinicMedical', 'faCloudRain', 'faCocktail', 
    'faCookieBite', 'faCouch', 'faCrow', 'faCross', 'faCrown', 'faCut', 'faCube', 'faDesktop', 'faDog', 'faDoorOpen', 'faDragon', 'faEdit', 
    'faEgg', 'faEnvelope', 'faEraser', 'faFaucet', 'faFemale', 'faJetFighter', 'faFile',  'faFilm', 'faFire', 'faFish', 'faFilter', 'faFlag',
    'faFolderOpen', 'faFrog', 'faFutbol', 'faGamepad', 'faGlassCheers', 'faGlasses', 'faGrin', 'faGraduationCap', 'faGrinTongue','faHamburger',
    'faHammer', 'faHandPaper', 'faHatCowboy', 'faHeart', 'faHippo', 'faHome', 'faHorse', 'faHotdog', 'faIceCream', 'faIgloo', 'faKey', 'faKiss', 
    'faLaptop', 'faLemon', 'faLightbulb', 'faLock',  'faMale', 'faMedkit', 'faMicrochip', 'faMicrophone', 'faMeteor', 'faMedal',
    'faMoon', 'faMotorcycle', 'faMouse', 'faPaintbrush','faPalette', 'faPaperclip', 'faPaperPlane', 'faPray', 'faPen', 'faPepperHot','faPercent',
    'faPhone', 'faPlane',  'faPizzaSlice', 'faPlay', 'faPlug', 'faPlusSquare', 'faPray', 'faPuzzlePiece', 'faQuestion', 'faRandom' ,'faQuestionCircle',
    'faRainbow', 'faRecycle', 'faRadiation', 'faRegistered', 'faRing', 'faRoad', 'faRobot', 'faRocket', 'faRunning', 'faSave',
    'faSchool', 'faSdCard', 'faSearch', 'faSeedling', 'faShoppingBag', 'faShoppingBasket', 'faShoppingCart', 'faShuttleVan', 'faSkiing', 
    'faSkull', 'faSmile', 'faSmog', 'faSmoking', 'faSnowflake', 'faSocks', 'faStar',  'faStethoscope', 'faStarOfDavid', 'faSwimmer', 'faSyringe',
    'faTableTennis',  'faTasks', 'faTaxi', 'faTeethOpen', 'faTachometer', 'faTheaterMasks', 'faTemperatureLow', 'faTemperatureHigh',
    'faToilet', 'faToiletPaper', 'faTools', 'faTooth', 'faTractor', 'faTrafficLight',  'faTram', 'faTrash', 'faTree','faTrophy', 'faTruck', 'faTruckPickup',
    'faTv', 'faTshirt', 'faUmbrella', 'faUser', 'faUserEdit', 'faUserFriends', 'faUtensils', 'faWallet', 'faWineBottle', 'faYinYang', 
     'faImage', 'faDiceOne','fa1', 'fa2', 'fa3', 'fa4', 'fa5', 'fa6', 'fa7', 'fa8', 'fa9', 'fa0','faDiceTwo','faDoorClosed', 'faShare');
    */
    public $iconos = array( '376','377','378','379','380',
    '381','382','383','384','385','386','387','388','389','390','391','392','393','394','395','396',
    '397','398','399','400','401','402','403','404','405','406','407','408','409','410','411','412',
    '413','414','415','416','417','418','419','420','421','422','423','424','425','426','427','428',
    '429','430','431','432','433','434','435','436','437','438','439','440','441','442','443','444',
    '445','446','447','448','449','450','451','452','453','454','455','456','457','458','459','460',
    '461','462','463','464','465','466','467','468','469','470','471','472','473','474','475');


    public function run()
    {
        // Deshabilitar verificaciones de clave foránea
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Ficha::truncate();
        foreach( $this->iconos as $key => $icono){
            Ficha::create([
                'name'=> $icono,
                'description'=>'icono'.$icono,
                'image'=> $icono.'.png',
                'sound'=> '',
                'active'=>1,
                'start_date'=>date("Y-m-d H:i:s"),
                ]
            );
        }
    }
}
