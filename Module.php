<?php

namespace MerapiPanel\Module\Setting;

use Exception;
use MerapiPanel\Box\Module\__Fragment;
use MerapiPanel\Box\Module\Entity\Module as EntityModule;
use MerapiPanel\Box\Module\ModuleLoader;
use Symfony\Component\Filesystem\Path;

class Module extends __Fragment
{
    protected $module;
    function onCreate(EntityModule $module)
    {
        $this->module = $module;
    }

    function listpops()
    {
        $modules = [];
        foreach (glob($_ENV['__MP_APP__'] . "/Module/*", GLOB_ONLYDIR) as $directory) {
            $manifest = [];
            if (file_exists(Path::join($directory, "manifest.json"))) {
                $manifest = json_decode(file_get_contents(Path::join($directory, "manifest.json")), 1);
            }
            if (isset($manifest['icon']) && !strpos($manifest['icon'], "http")) {
                $manifest['icon'] = "/" . Path::makeRelative(Path::join($directory, $manifest['icon']), $_ENV["__MP_CWD__"]);
            }

            $modules[] = [
                "location" => $directory,
                "name"     => basename($directory),
                "manifest" => $manifest,
                "active"   => in_array(basename($directory), ModuleLoader::getDefaultModules()) || is_file(Path::join($directory, '.active')),
            ];
        }
        return $modules;
    }


    function getpop($module_name)
    {

        $directory = Path::join($_ENV['__MP_APP__'], "Module", $module_name);
        if (!is_dir($directory)) throw new Exception("Module {$module_name} does't exist", 404);

        $manifest = [];
        if (file_exists(Path::join($directory, "manifest.json"))) {
            $manifest = json_decode(file_get_contents(Path::join($directory, "manifest.json")), 1);
        }
        if (isset($manifest['icon']) && !strpos($manifest['icon'], "http")) {
            $manifest['icon'] = "/" . Path::makeRelative(Path::join($directory, $manifest['icon']), $_ENV["__MP_CWD__"]);
        }

        $module["location"] = $directory;
        $module["name"]     = basename($directory);
        $module['manifest'] = $manifest;
        $module['active']   = in_array($module_name, ModuleLoader::getDefaultModules()) || is_file(Path::join($directory, '.active'));
        $module['readme'] = file_exists(Path::join($directory, "README.md")) ? file_get_contents(Path::join($directory, "README.md")) : "";

        return $module;
    }
}
