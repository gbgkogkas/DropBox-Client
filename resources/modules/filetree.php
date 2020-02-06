<?php
    global $dropBoxClient;

    $dropBoxHomeDirectory = $dropBoxClient->list_folder(['path' => '']);

    function buildFileTree () {
        global $dropBoxHomeDirectory;

        $folders = array();
        $files = array();

        foreach ($dropBoxHomeDirectory['entries'] as $index => $entry) {
            switch ($entry['.tag']) {
                case "folder":
                    array_push($folders, '<li><a href="#" class="folder" data-path="' . $entry['path_lower'] . '">' . trim($entry['name']) . '</a></li>');
                    break;
                case "file":
                    array_push($files, '<li><a href="#">' . $entry['name'] . '</a></li>');
                    break;
            }
        }

        sort($folders);
        sort($files);

        echo '<ul class="tree-structure">' , rtrim(join('', $folders)) , rtrim(join('', $files)) ,'</ul>';
    }

    function buildFolderDisplay () {
        global $dropBoxHomeDirectory;

        $folders = array();
        $files = array();

        foreach ($dropBoxHomeDirectory['entries'] as $index => $entry) {
            switch ($entry['.tag']) {
                case "folder":
                    array_push($folders, buildFolderDisplayItem($entry));
                    break;
                case "file":
                    array_push($files, buildFolderDisplayItem($entry));
                    break;
            }
        }

        echo '<ul class="folder-display">' , rtrim(join('', $folders)) , rtrim(join('', $files)) ,'</ul>';
    }

    function buildFolderDisplayItem ($entry) {
        $item = '<li class="folder-display-element">';
        $item .= '<div class="folder-display-element-container">';

        if ($entry['.tag'] != 'folder') {
            $item .= '<div class="folder-display-element-overlay">';
            $item .= '<div class="folder-display-element-overlay-anchor"><div class="folder-display-element-overlay-button-holder">';
            $item .= '<a class="folder-display-element-overlay-button download" href="#" title="download" data-name="' . $entry['name'] . '"><img src="public\img\download.png"></a>';
            $item .= '<a class="folder-display-element-overlay-button delete" href="#" title="delete"><img src="public\img\delete.png"></a>';
            $item .= '</div></div>';
            $item .= '</div>';
        }

        $item .= '<a class="folder-display-element-item ' . ($entry['.tag'] == 'folder' ? 'folder-trigger' : '') . '" href="#" title="' . $entry['name'] . '" data-path="' . $entry['path_lower'] . '">';
        $item .= '<div class="folder-display-element-icon"><img src="public\img\placeholder.png" alt=""></div>';
        $item .= '<div class="folder-display-element-name"><div class="folder-display-element-name-anchor"><span>' . $entry['name'] . '</span></div></div>';
        $item .= '</a>';
        $item .= '</div>';
        $item .= '</li>';

        return $item;
    }
