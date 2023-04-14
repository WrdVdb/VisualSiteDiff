<?php
 
namespace App\Http\Controllers;
  
use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;
  
class DiffController extends Controller
{
    /**
     * Write code on Method
     *
     * @return response()
     */

    public function index(Request $request, String $config){
        $config_data = $this->getConfigData($config);
        if($config_data === false){return view('confignotfound', ['config'=>$config]);}
        $config_run = $this->getConfigRun($config);
        $config_run_path = base_path('storage/app/diff/'.$config.'.json');

        $hasChanges = false;
        $configHasChanged = false;
        foreach($config_data['urls'] as $index => $urlinfo){
            $width = (isset($urlinfo['width']) ? $urlinfo['width'] : 1920);
            if(!isset($config_run[$urlinfo['url'].'--'.$width])){
                $this->create($request, $config, $index);
                $hasChanges = true;
            }
        }
        if($configHasChanged){
            if (!file_exists($config_run_path)) {touch($config_run_path);}
            $config_run = json_decode(file_get_contents($config_run_path), true);
        }
        if($hasChanges){
            if (!file_exists($config_run_path)) {touch($config_run_path);}
            $config_run = json_decode(file_get_contents($config_run_path), true);
        }
        return view('index', ['config'=>$config, 'config_data' => $config_data,'config_run' => $config_run]);
    }

    public function createAll(Request $request, String $config){
        $config_data = $this->getConfigData($config);
        if($config_data === false){return redirect()->route('diff.index', ['config' => $config]);}
        foreach($config_data['urls'] as $index => $urlinfo){
            $this->create($request, $config, $index);
        }
        return redirect()->route('diff.index', ['config' => $config]);
    }

    public function add(Request $request, String $config){
        $config_path = base_path('config/sites/'.$config.'.json');

        $config_data = $this->getConfigData($config);
        if($config_data === false){return redirect()->route('diff.index', ['config' => $config]);}
        $newurl = $request->input('newurl');

        foreach($config_data['domains'] as $domain){
            $newurl = str_replace($domain, '', $newurl);
        }
        $width = $request->input('width');
        if($width == ''){$width = 1920;}
        if($newurl != ''){
            if(substr($newurl, 0, 4) != 'http'){
                if(substr($newurl, 0, 1) != '/'){
                    $newurl = '/'.$newurl;
                }
                $urlfound = false;
                foreach($config_data['urls'] as $index => $urlinfo){
                    if($newurl == $urlinfo['url'] && $width == $urlinfo['width']){
                        $urlfound = true;
                        break;
                    }
                }
                if(!$urlfound){
                    $config_data['urls'][] = ['url'=>$newurl,'width'=>$width];
                    file_put_contents($config_path, json_encode($config_data, JSON_PRETTY_PRINT));
                }
            }
        }

        return redirect()->route('diff.index', ['config' => $config]);
    }

    public function compare(Request $request, String $config, int $index){
        $config_data = $this->getConfigData($config);
        if($config_data === false){return redirect()->route('diff.index', ['config' => $config]);}
        $config_run = $this->getConfigRun($config);
        $config_run_for_url =  $config_run[$config_data['urls'][$index]['url'].'--'.$config_data['urls'][$index]['width']];
        return view('compare', ['config'=>$config, 'diff' => $config_run_for_url['diffimage'], 'img1' => $config_run_for_url['image1'], 'img2' => $config_run_for_url['image2']]);
    }

    public function create(Request $request, String $config, int $index = null){

        if($index === null){
            //From Post
            $indexes = $request->input('indexes');
        }else{
            //From Get
            $indexes = [$index];
        }
        
        if(is_array($indexes)){
            $config_data = $this->getConfigData($config);
            if($config_data === false){return redirect()->route('diff.index', ['config' => $config]);}
            $config_run = $this->getConfigRun($config);
            
            if (!file_exists(base_path('public/screenshots/'. $config))) {mkdir(base_path('public/screenshots/'. $config));}
            if (!file_exists(base_path('public/screenshots/'. $config.'/diff'))) {mkdir(base_path('public/screenshots/'. $config.'/diff'));}
            if (!file_exists(base_path('public/screenshots/'. $config.'/source'))) {mkdir(base_path('public/screenshots/'. $config.'/source'));}

            foreach($indexes as $index){
                $urlinfo = $config_data['urls'][$index];
                $url = $urlinfo['url'];
                $width = (isset($urlinfo['width']) ? $urlinfo['width'] : 1920);
                $disableImages = (isset($urlinfo['disableImages']) ? $urlinfo['disableImages'] : false);
                $addScriptTag = (isset( $config_data['addScriptTag']) ?  $config_data['addScriptTag'] : '');
                $addScriptTag = (isset( $urlinfo['addScriptTag']) ?  $urlinfo['addScriptTag'] : $addScriptTag);
                $filepaths = [];
                $fileurls = [];
                //Per domein een screenshot maken
                foreach($config_data['domains'] as $d => $domain){
                    $fullurl = $domain.$url;
                    $filename = $this->url_to_filename($fullurl,$width);
                    $filepath = base_path('public/screenshots/'. $config .'/source/'.$filename);
                    $filepaths[] = $filepath;
                    $fileurls[] = '/screenshots/'. $config .'/source/'.$filename;
                    $bs = Browsershot::url($fullurl)
                        ->setOption('landscape', true)
                        ->setOption('highlight-color', 'SeaGreen')
                        ->ignoreHttpsErrors()
                        ->windowSize($width, 2160)
                        ->fullPage();
                    if($disableImages){
                        $bs->disableImages();
                    }
                    $bs->waitUntilNetworkIdle(false);
                    if($addScriptTag){
                        $bs->setOption('addScriptTag', json_encode($addScriptTag));
                    }
                    //

                    $bs->save($filepath);
                }

                //Diff image maken
                

                $image1 = new \imagick();
                $image2 = new \imagick();

                // set the fuzz factor (must be done BEFORE reading in the images)
                $image1->SetOption('fuzz', '4%');
                //$image1->SetOption('compose', 'Src'); //No shadow of second image

                $image1->readImage($filepaths[0]);
                $image2->readImage($filepaths[1]);

                $result = $image1->compareImages($image2, 1);
                //dump($result);

                $diffimage = $result[0];

                $diffFilename = str_replace(['/','.'],'-',substr($url,1)).'--'.$width;
                $diffimage->setImageFormat("png");
                $diffimage->writeImage(base_path('public/screenshots/'. $config .'/diff/'.$diffFilename.'.png'));

                $diffimage->thumbnailImage(200, 200, true, true);
                $diffimage->writeImage(base_path('public/screenshots/'. $config .'/diff/'.$diffFilename.'--thumb.png'));

                $config_run[$url.'--'.$width] = [
                    'image1' => $fileurls[0],
                    'image2' => $fileurls[1],
                    'diffimage' => '/screenshots/'. $config .'/diff/'.$diffFilename.'.png',
                    'diffimage_thumb' => '/screenshots/'. $config .'/diff/'.$diffFilename.'--thumb.png',
                    'diff_score' => $result[1],
                    'lastrun' => time()
                ];

                $config_run_path = base_path('storage/app/diff/'.$config.'.json');
                if (!file_exists($config_run_path)) {touch($config_run_path);}
                file_put_contents($config_run_path, json_encode($config_run, JSON_PRETTY_PRINT));
            }
        }
        return redirect()->route('diff.index', ['config' => $config]);
    }

    function url_to_filename($url,$width){
        $filename = str_replace(['/','.','?','#'],'-',str_replace(['https://','www.'],'',$url)).'--'. $width .'.png';
        return $filename;
    }

    function getConfigData($config){
        $config_path = base_path('config/sites/'.$config.'.json');
        if(file_exists($config_path)){
            $config_data = json_decode(file_get_contents($config_path), true);
            $configHasChanged = false;
            foreach($config_data['urls'] as $index => $urlinfo){
                if(!is_array($urlinfo)){
                    $config_data['urls'][$index] = ['url' => $urlinfo, 'width'=> 1920];
                    $configHasChanged = true;
                }else{
                    if(!isset($config_data['urls'][$index]['width'])){
                        $config_data['urls'][$index]['width'] = 1920;
                        $configHasChanged = true;
                    }
                }
            }
            if($configHasChanged){
                file_put_contents($config_path, json_encode($config_data, JSON_PRETTY_PRINT));
            }
            return $config_data;
        }
        return false;
    }

    function getConfigRun($config){
        $config_run_path = base_path('storage/app/diff/'.$config.'.json');
        if(file_exists($config_run_path)){
            $config_run = json_decode(file_get_contents($config_run_path), true);
            return $config_run;
        }else{
            return[];
        }
    }
}