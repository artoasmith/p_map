<?php

namespace PokemonBundle\Base;

use Symfony\Component\Yaml\Parser;

class UploaderEntity
{
    public function documentRoot(){
        $yaml = new Parser();
        $a = $yaml->parse(file_get_contents(__DIR__ . '\..\..\..\app\config\params.yml'));
        return (isset($a['document_root'])?$a['document_root']:'');
    }

    /**
     * @return string
     */
    public function defaultFolderPath(){
        return '/upload/defaultFolderPath/';
    }

    public function clearOldUpload($type){
        if(!$type)
            return;

        $file = $this->documentRoot().$this->defaultFolderPath().$this->{'get'.$type}();
        @unlink($file);
    }

    public function upload($type, $fileurl = ''){
        if (empty($fileurl)) {
            if (null === $this->{'getFile' . $type}()) {
                return;
            }

            $filename = md5(time() . rand(5, 100)) . '.' . pathinfo($this->{'getFile' . $type}()->getClientOriginalName(), PATHINFO_EXTENSION);
            $filePath = $this->defaultFolderPath();
            $filename = str_replace('..', '.', $filename);


            $this->clearOldUpload($type);

            $file = $this->{'getFile' . $type}();
            $newfile = $this->documentRoot() . $filePath . $filename;

            if (\copy($file, $newfile)) {
                $this->{'set' . $type}($filename);
            }
        } else {
            $arr = @file($fileurl);
            if (!empty($arr)) {

                $pathinfo = pathinfo($fileurl);
                if (strpos($pathinfo['extension'], '?') !== false)
                    $filename = md5(time() . rand(5, 100)) . '.' . substr($pathinfo['extension'], 0, strpos($pathinfo['extension'], '?'));
                else
                    $filename = md5(time() . rand(5, 100)) . '.' . $pathinfo['extension'];

                $filePath = $this->defaultFolderPath();
                $filename = str_replace('..', '.', $filename);

                if (strpos($fileurl, '?') !== false) {
                    $filename = substr($filename, 0, strpos($fileurl, '?'));
                }

                $this->clearOldUpload($type);

                $newfile = $this->documentRoot() . $filePath . $filename;
                if (\copy($fileurl, $newfile)) {
                    $this->{'set' . $type}($filename);
                }
            }
        }
    }
}