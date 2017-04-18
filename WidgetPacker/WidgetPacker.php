<?php


namespace KamillePacker\WidgetPacker;


use Bat\FileSystemTool;
use KamillePacker\Config\ConfigInterface;

class WidgetPacker
{

    /**
     * @var ConfigInterface
     */
    private $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public static function create(ConfigInterface $config)
    {
        return new static($config);
    }

    public function pack($name)
    {
        $appDir = $this->config->get('appDir');
        $targetDir = $this->getTargetDir($appDir);

        /**
         * updateMode: if true (default), will replace files/app files with the new one.
         * The readme file will be left unchanged anyway.
         */
        $updateMode = $this->config->get('updateMode', true, false);


        //--------------------------------------------
        // TARGET DIR
        //--------------------------------------------
        $itemTargetDir = $targetDir . "/" . $name;
        if (!is_dir($itemTargetDir)) {
            FileSystemTool::mkdir($itemTargetDir, 0777, true);
        }

        //--------------------------------------------
        // FILES
        //--------------------------------------------
        $entriesToPack = $this->getEntriesToPack($appDir, $name);
        $lenAppDir = mb_strlen($appDir);
        foreach ($entriesToPack as $path) {

            if (false === $updateMode && file_exists($path)) {
                continue;
            }

            $relPath = substr($path, $lenAppDir);
            $targetEntry = $itemTargetDir . "/files/app" . $relPath;
            $c = file_get_contents($path);
            FileSystemTool::mkfile($targetEntry, $c);
        }

        //--------------------------------------------
        // README
        //--------------------------------------------
        $readmeTarget = $itemTargetDir . "/README.md";
        if (!file_exists($readmeTarget)) {

            $readmeTpl = $this->getReadmeTemplatePath();
            $vars = [
                "{name}" => lcfirst($name),
                "{Name}" => $name,
                "{date}" => date("Y-m-d"),
            ];
            $c = file_get_contents($readmeTpl);
            $c = str_replace(array_keys($vars), array_values($vars), $c);
            FileSystemTool::mkfile($readmeTarget, $c);
        }


        //--------------------------------------------
        // INSTALLER FILE
        //--------------------------------------------
        $installerClassTarget = $itemTargetDir . "/" . $this->getInstallerClassTargetRelativePath($name);
        if (!file_exists($installerClassTarget)) {

            $installerTpl = $this->getInstallerTemplatePath();
            $vars = [
                "{name}" => lcfirst($name),
                "{Name}" => $name,
                "{date}" => date("Y-m-d"),
            ];
            $c = file_get_contents($installerTpl);
            $c = str_replace(array_keys($vars), array_values($vars), $c);
            FileSystemTool::mkfile($installerClassTarget, $c);
        }


    }


    //--------------------------------------------
    //
    //--------------------------------------------
    /**
     * returns locations of entries to pack
     */
    protected function getEntriesToPack($appDir, $name)
    {
        // override this per implementation
        // here I'm doing the Widget implementation

        $name = lcfirst($name);
        $ret = [];

        $laws2CssFile = $appDir . "/www/theme/_default_/widgets/$name/$name.default.css";
        $tplFile = $appDir . "/theme/_default_/widgets/$name/default.tpl.php";
        if (file_exists($laws2CssFile)) {
            $ret[] = $laws2CssFile;
        }
        if (file_exists($tplFile)) {
            $ret[] = $tplFile;
        }

        return $ret;
    }

    protected function getTargetDir($appDir)
    {
        return $appDir . "/class-widgets";
    }

    protected function getReadmeTemplatePath()
    {
        return __DIR__ . "/assets/README.tpl.md";
    }

    protected function getInstallerTemplatePath()
    {
        return __DIR__ . "/assets/InstallerClass.tpl.php";
    }

    protected function getInstallerClassTargetRelativePath($name)
    {
        return $name . "WidgetInstaller.php";
    }
}