<?php

class Bookmark {
    private $file;

    public function __construct($file) {
        $this->file = $file;

        if (!file_exists($this->file)) {
            $this->save();
        }
    }

    public function get() {
        return json_decode(
            file_get_contents($this->file),
            true
        );
    }

    public function add($path) {
        $bookmarks = $this->get();
        $bookmarks[] = $path;
        $bookmarks = array_unique($bookmarks);
        
        $this->save($bookmarks);        
    }
    
    public function delete($path) {
        $bookmarks = $this->get();
        $bookmarks = array_diff(
            $bookmarks,
            [ $path ]
        );
        
        $this->save($bookmarks);        
    }
    
    private function save($data = []) {
        return file_put_contents(
            $this->file,
            json_encode(
                array_values($data),
                JSON_PRETTY_PRINT
            )
        );
    }
}
