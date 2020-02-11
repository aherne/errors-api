<?php
class PHP7Converter
{
    private $results = [];
    
    public function __construct($folder)
    {
        $this->scan($folder);
        $this->save();
        echo "COMPLETE!";
    }
    
    private function scan($folder)
    {
        $files = scandir($folder);
        foreach ($files as $file) {
            if (in_array($file, [".", ".."])) {
                continue;
            } elseif (is_dir($folder."/".$file)) {
                // recurse
                $this->scan($folder."/".$file);
            } elseif (strpos($file, ".php")) {
                // open
                $filePath = $folder."/".$file;
                
                $returnType = "void";
                $parameterTypes = [];
                $handle = fopen($filePath, "r");
                while (!feof($handle)) {
                    $line = fgets($handle);
                    
                    $matches = [];
                    preg_match("/@return\s+([^\s]+)/", $line, $matches);
                    if ($matches) {
                        $returnType = $this->getType($filePath, $matches[1]);
                    }
                    
                    $matches = [];
                    preg_match("/@param\s+([^\s]+)\s+([^\s]+)/", $line, $matches);
                    if ($matches) {
                        $parameterTypes[$matches[2]] = $this->getType($filePath, $matches[1]);
                    }
                    
                    $matches = [];
                    preg_match("/function\s+([^\(]+)\((.*)\)/", $line, $matches);
                    if ($matches) {
                        $parameters = "";
                        if (!empty($matches[2])) {
                            $matches1 = [];
                            $parameters = $matches[2];
                            preg_match_all("/([a-zA-Z0-9\\\]+\s+)?\\$([a-zA-Z0-9\_]+)/", $matches[2], $matches1);
                            if ($matches1[0]) {
                                foreach ($matches1[2] as $i=>$variable) {
                                    $variable = '$'.$variable;
                                    if (!isset($parameterTypes[$variable])) {
                                        die($filePath.": ".$matches[1]." misses ".$variable);
                                    }
                                    if (empty($matches1[1][$i])) {
                                        $parameters = str_replace($matches1[0][$i], $parameterTypes[$variable]." ".$matches1[0][$i], $parameters);
                                    }
                                }
                            }
                        }
                        
                        $this->results[$filePath][$matches[1]]["parameters"] = $parameters;
                        $this->results[$filePath][$matches[1]]["returns"] = $returnType;
                        
                        $returnType = "void";
                        $parameterTypes = [];
                    }
                }
                fclose($handle);
            }
        }
    }
    
    private function save()
    {
        foreach ($this->results as $fileName=>$info) {
            file_put_contents($fileName, preg_replace_callback("/function\s+([^\(]+)\((.*)\)/", function ($matches) use ($info) {
                return "function ".$matches[1]."(".$info[$matches[1]]["parameters"]."): ".$info[$matches[1]]["returns"];
            }, file_get_contents($fileName)));
        }
    }
    
    private function getType($filePath, $string)
    {
        $type = "";
        if ($type=="void" || !$string) {
            $type = "void";
        } elseif ($string=="boolean") {
            $type = "bool";
        } elseif ($string=="integer") {
            $type = "int";
        } elseif ($string=="double") {
            $type = "float";
        } elseif ($string=="mixed") {
            $type = "mixed";
        } elseif (in_array($string, ["array", "string"])) {
            $type = $string;
        } elseif (strpos($string, "[")!==false) {
            $type = "array";
        } elseif (ctype_upper($string[0]) || $string[0]==="\\") {
            $type = $string;
        } else {
            die($filePath.": ".$string." is invalid");
        }
        return $type;
    }
}

class AutoloadConverter
{
    private $classes = [];
    private $results = [];
    private $regex;
    
    public function __construct($folder, $namespace)
    {
        $this->setClasses($folder, $namespace);
        $this->setRegex();
        $this->setDependencies($folder);
        $this->save();
        echo "COMPLETE!";
    }
    
    private function setRegex()
    {
        $classNames = array_keys($this->classes);
        usort($classNames, function ($a, $b) {
            return strlen($b)-strlen($a);
        });
        $this->regex = "/(\s|\()(".implode("|", $classNames).")/";
    }
    
    private function setClasses($folder, $namespace)
    {
        $files = scandir($folder);
        foreach ($files as $file) {
            if (in_array($file, [".", ".."])) {
                continue;
            } elseif (is_dir($folder."/".$file)) {
                // recurse
                $this->setClasses($folder."/".$file, $namespace."\\".$file);
            } elseif (strpos($file, ".php")) {
                $className = str_replace(".php", "", $file);
                $this->classes[$className]["folder"] = $folder;
                $this->classes[$className]["namespace"] = $namespace;
                $this->classes[$className]["dependencies"] = [];
            }
        }
    }
    
    private function setDependencies($folder)
    {
        $files = scandir($folder);
        foreach ($files as $file) {
            if (in_array($file, [".", ".."])) {
                continue;
            } elseif (is_dir($folder."/".$file)) {
                // recurse
                $this->setDependencies($folder."/".$file);
            } elseif (strpos($file, ".php")) {
                $className = str_replace(".php", "", $file);
                $matches = [];
                preg_match_all($this->regex, file_get_contents($folder."/".$file), $matches);
                $dependencies = [];
                if (!empty($matches[2])) {
                    foreach ($matches[2] as $dependency) {
                        if ($className==$dependency) {
                            continue;
                        }
                        $dependencies[$dependency] = $dependency;
                    }
                }
                $this->classes[$className]["dependencies"] = $dependencies;
            }
        }
    }
    
    private function save()
    {
        foreach ($this->classes as $className=>$info) {
            $fileName = $info["folder"]."/".$className.".php";
            $contents = preg_replace_callback("/namespace\s+([^;]+);/", function ($matches) use ($info) {
                $result = "namespace ".$info["namespace"].";\r\n\r\n";
                foreach ($info["dependencies"] as $dependencyClassName) {
                    if ($this->classes[$dependencyClassName]["namespace"] != $info["namespace"]) {
                        $result .= "use ".$this->classes[$dependencyClassName]["namespace"]."\\".$dependencyClassName.";\r\n";
                    }
                }
                return $result;
            }, file_get_contents($fileName));
            $contents = preg_replace("/(?:require|include)(?:_once)?[( ]['\"](.*)\.php['\"]\)?;/", "", $contents);
            $contents = preg_replace("/\\n{2,}/", "\n\n", $contents);
            file_put_contents($fileName, $contents);
        }
    }
}

// new PHP7Converter("src");
new AutoloadConverter("src", "Lucinda\STDERR");
