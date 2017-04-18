<?php


namespace KamillePacker\ModulePacker;


use Bat\FileSystemTool;
use DirScanner\YorgDirScannerTool;
use KamillePacker\Packer\AbstractPacker;

class ModulePacker extends AbstractPacker
{


    protected function specificPack($name, $appDir, $updateMode, $itemTargetDir)
    {
        //--------------------------------------------
        // CONF FILE
        //--------------------------------------------
        $appConfFile = "$appDir/config/modules/$name.conf.php";
        if (file_exists($appConfFile)) {
            $itemConfFile = "$itemTargetDir/conf.php";
            copy($appConfFile, $itemConfFile);
        }

        //--------------------------------------------
        // CONTROLLERS
        //--------------------------------------------
        $appControllersDir = "$appDir/class-controllers/$name";
        if (is_dir($appControllersDir)) {
            $appControllers = YorgDirScannerTool::getFilesWithExtension($appControllersDir, "php", false, true, true);
            if (count($appControllers) > 0) {
                $itemControllerDir = "$itemTargetDir/Controller";
                foreach ($appControllers as $appController) {
                    $appControllerPath = $appControllersDir . "/" . $appController;
                    $itemController = $itemControllerDir . "/" . $appController;
                    FileSystemTool::mkfile($itemController, file_get_contents($appControllerPath));
                }
            }
        }


    }


    //--------------------------------------------
    //
    //--------------------------------------------
    protected function getTargetDir($appDir)
    {
        return $appDir . "/class-modules";
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
        return $name . "ModuleInstaller.php";
    }
}