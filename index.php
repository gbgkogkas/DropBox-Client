<?php
    session_start();

    include('app/DropBoxClient.php');

    if (array_key_exists('dropbox-code', $_SESSION)) {
        $dropBoxClient = new dropBoxClient();
    }

    include('resources/modules/filetree.php');

    global $dropBoxHomeDirectory;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>DropBox App</title>

    <style>
        html, body {
            margin: 0;
            padding: 0;

            height: 100vh;
        }

        main{
            background-color: #8cbed6;

            height: 100vh;
        }

        .tree-structure {
            margin: 0;
            padding: 0;

            list-style: none;
        }

        .tree-structure .tree-structure {
            padding-left: 1rem;
        }

        .row {
            display: inline-flex;
            width: 100%;
        }

        .row .column-20{
            width: 20%;
        }

        .row .column-30{
            width: 30%;
        }

        .row .column-60{
            width: 60%;
        }

        .row .column-70{
            width: 70%;
        }

        .tree-display {
            height: 100vh;
        }

        .tree-display-element {
            margin: 1rem;
            padding: 1rem;

            border-radius: 1em;

            background-color: rgba(255,255,255,0.75);

            overflow: auto;
        }

        .tree-display-element.no-background {
            background-color: initial;
        }

        .tree-display-element .vertical-header, .tree-display-element .vertical-body {
            border-radius: 1em;
        }

        .tree-display-element .vertical-header {
            padding: 1rem;
            margin-bottom: 0.5em;
            height: 2%;
            background-color: rgba(255,255,255,0.75);
        }

        .tree-display-element .vertical-body {
            margin-top: 0.5em;
            height: 93%;
            background-color: rgba(255,255,255,0.75);
        }

        .folder-display {
            display: inline-flex;
        }

        .folder-display {
            width: 100%;

            margin: 0;
            padding: 0;

            display: inline-table;
            list-style: none;
            text-align: center;
        }

        .folder-display .folder-display-element{
            width: 20%;

            margin: 0;
            padding: 0;

            display: inline-block;
            text-align: center;
            float: left;
        }

        .folder-display .folder-display-element .folder-display-element-item{
            text-decoration: none;
            color: black;
        }

        .folder-display .folder-display-element .folder-display-element-container {
            position: relative;
            display: table;
            height: 10em;

            box-shadow: 4px 5px rgba(0,0,0,0.4);
            margin: 1rem;
        }

        .folder-display .folder-display-element .folder-display-element-container .folder-display-element-overlay{
            position: absolute;
            width: 100%;
            height: 2em;
            max-height: 0;

            background-color: rgba(116, 120, 128, 0.25);

            overflow: hidden;
            z-index: 1;
        }

        .folder-display .folder-display-element:hover .folder-display-element-container .folder-display-element-overlay{
            max-height: 100%;
        }

        .folder-display .folder-display-element .folder-display-element-container .folder-display-element-overlay .folder-display-element-overlay-anchor {
            width: 100%;
            height: 100%;

            display: table;
        }

        .folder-display .folder-display-element .folder-display-element-container .folder-display-element-overlay .folder-display-element-overlay-anchor .folder-display-element-overlay-button-holder {
            width: 100%;

            display: table-cell;
            vertical-align: middle;
        }

        .folder-display .folder-display-element .folder-display-element-container .folder-display-element-overlay .folder-display-element-overlay-anchor .folder-display-element-overlay-button-holder .folder-display-element-overlay-button {
            margin: 0 0.5em;
        }

        .folder-display .folder-display-element .folder-display-element-container .folder-display-element-overlay img{
            width: 1em;
            height: 1em;
        }

        .folder-display .folder-display-element .folder-display-element-container .folder-display-element-icon {
            margin-top: 0.25em;
        }

        .folder-display .folder-display-element .folder-display-element-container .folder-display-element-icon img {
            width: 70%;
            height: 7.25em;
        }

        .folder-display .folder-display-element .folder-display-element-container .folder-display-element-name {
            display: table;

            position: absolute;
            bottom: 0;

            height: 2em;
            width: 100%;
        }

        .folder-display .folder-display-element .folder-display-element-container .folder-display-element-name .folder-display-element-name-anchor{
            vertical-align: middle;
            display: table-cell;
        }

        .folder-display .folder-display-element .folder-display-element-container .folder-display-element-name .folder-display-element-name-anchor span{
            overflow: hidden;
            max-height: 1em;
            max-width: 80%;
            display: inline-block;
        }

        .folder-display .folder-display-element .folder-display-element-container .folder-display-element-overlay, .folder-display .folder-display-element:hover .folder-display-element-container .folder-display-element-overlay {
            transition: max-height 0.5s linear 0s;
        }

        .button-list {
            display: inline-flex;

            margin: 0;
            padding: 0;

            list-style: none;
        }

        .button {
            background-color: #1186cf;
            padding: 0.25rem 0.5rem;
            color: white;
            text-decoration: none;
        }

        .overlay {
            display: none;

            position: absolute;
            top: 0;
            left: 0;

            height: 100%;
            width: 100%;

            padding: 0;
            margin: 0;

            z-index: 5;

            background-color: rgba(0,0,0,0.5);
        }

        .overlay.one-up {
            z-index: 15;
        }

        .modal{
            display: none;

            position: absolute;
            z-index: 10;

            background-color: white;
            border-radius: 1rem;
        }

        .modal .modal-container{
            position: relative;

            width: 400px;
            height: 400px;
        }

        .modal .modal-container .modal-head{
            height: 50px;
        }

        .modal .modal-container .modal-body{
            height: 300px;
        }

        .modal .modal-container .modal-body form{
            padding: 1rem;
            height: 268px;
        }

        .modal .modal-container .modal-controls{
            width: 100%;
            height: 50px;
            display: table;
        }

        .modal .modal-container .modal-controls .modal-controls-anchor{
            width: 100%;
            display: table-cell;
            vertical-align: middle;
        }

        .modal .modal-container .modal-controls .modal-controls-anchor .button-list{
            float: right;
            padding-right: 8px;
        }

        .modal .modal-container .modal-close{
            position: absolute;
            right: 0.5rem;
            top: 0.5rem;

            background-color: red;
            padding: 0.25rem 0.5rem;
            border-radius: 1rem;

            text-align: center;
            text-decoration: none;
            color: white;
        }

        .no-vertical-padding {
            padding-top: 0;
            padding-bottom: 0;
        }
    </style>
</head>
<body>
    <main>
        <?php if (! array_key_exists('dropbox-code', $_SESSION)) { ?>
            <a href="https://www.dropbox.com/oauth2/authorize?client_id=s8fn9zrw29dcfk6&response_type=code&redirect_uri=http://localhost/dropbox/tokenVerified.php">DropBox</a>
        <?php } else { ?>
            <div class="row tree-display">
                <div class="column-20 tree-display-element">
                    <ul class="tree-structure">
                        <li>
                            <a href="#" class="folder active" data-path="/">Home</a>
                            <?php buildFileTree(); ?>
                        </li>
                    </ul>
                </div>
                <div class="column-70 tree-display-element no-background no-vertical-padding">
                    <div class="vertical-header">
                        <ul class="button-list" style="float: right">
                            <li><a href="#" class="button" data-modal-trigger="#upload-modal">Upload</a></li>
                        </ul>
                    </div>
                    <div class="vertical-body">
                        <?php buildFolderDisplay(); ?>
                    </div>
                </div>
            </div>
        <?php } ?>
    </main>

    <div class="overlay"></div>
    <div id="upload-modal" class="modal">
        <div class="modal-container">
            <div class="modal-head"></div>
            <div class="modal-body">
                <form action="/dropbox/file.php?action=upload">
                    <input type="hidden" id="path" name="path" value="/">
                    <label for="upload-file">Select a file to upload</label>
                    <input id="upload-file" name="file" type="file" style="margin-top: 4px">
                </form>
            </div>
            <div class="modal-controls">
                <div class="modal-controls-anchor">
                    <ul class="button-list">
                        <li><a id="submit-upload" href="#" class="button">Upload</a></li>
                    </ul>
                </div>
            </div>
            <a href="#" class="modal-close">X</a>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function () {
            var paths = {
                '/' : <?php echo json_encode($dropBoxHomeDirectory['entries']) ?>
            };

            $(document)
                .on('click', '.folder, .folder-trigger', function (e) {
                    e.preventDefault();

                    var obj = $(this);

                    open_folder($('.tree-structure [data-path="' + obj.data('path') + '"]'));
                })
                .on('click', '.modal-close', function (e) {
                    e.preventDefault();

                    $(this).closest('.modal').modal_close();
                })
                .on('click', '[data-modal-trigger]', function (e) {
                    e.preventDefault();

                    $($(this).data('modal-trigger')).modal_open();
                })
                .on('click', '.folder-display-element-overlay-button.delete', function (e) {
                    e.preventDefault();

                    var obj = $(this),
                        path = $('.folder.active').data('path');

                    $.ajax({
                        'method'        : 'POST',
                        'url'           : '/dropbox/file.php?action=delete',
                        'dataType'      : 'JSON',
                        'data'          : {
                            'source_path'   : path,
                            'path'          : obj.closest('.folder-display-element').find('.folder-display-element-item').data('path')
                        },
                        'success'       : function (data) {
                            if (data.success) {
                                updateDisplays(obj, data);
                            }
                        }
                    });
                })
                .on('click', '.folder-display-element-overlay-button.download', function (e) {
                    e.preventDefault();

                    var obj = $(this),
                        path = $('.folder.active').data('path'),
                        name = obj.data('name');

                    $.ajax({
                        'method'        : 'POST',
                        'url'           : '/dropbox/file.php?action=download',
                        'dataType'      : 'JSON',
                        'data'          : {
                            'path'  : path,
                            'name'  : name
                        },
                        'success'       : function (data) {
                            console.log(data);
                            if (data.success) {
                                $('body').append('<iframe id="download-' + name + '" src="download.php" style="display: none"/>');
                            }
                        }
                    });
                });

            $('#submit-upload').on('click', function (e) {
                e.preventDefault();

                var obj = $(this),
                    modal = obj.closest('.modal'),
                    form = modal.find('form'),
                    formData = new FormData(),
                    path = form.find('#path').val(),
                    uploadFile = form.find('#upload-file');

                formData.append('name', uploadFile[0].files[0]);
                formData.append('path', path);

                $.ajax({
                    'method'        : 'POST',
                    'url'           : form.attr('action'),
                    'cache'         : false,
                    'contentType'   : false,
                    'processData'   : false,
                    'dataType'      : 'JSON',
                    'data'          : new FormData(form[0]),
                    'success'       : function (data) {
                        if (data.success) {
                            var treeDisplayItem = $('.tree-structure [data-path="' + path + '"]');

                            paths[path] = data.data.entries;
                            addSubFolderToTree(treeDisplayItem, data.data.entries);
                            openFolderToDisplay (treeDisplayItem, data.data.entries);

                            uploadFile.val('');
                            modal.modal_close();

                        }
                    }
                });
            });

            $.fn.extend({
                'modal_open': function () {
                    modal_open($(this));
                    overlay_open();
                },
                'modal_close': function () {
                    modal_close($(this));
                    overlay_close();
                },
            });

            function modal_open (obj) {
                var viewPortHeight = $(window).height(),
                    viewPortWidth = $(window).width(),
                    modalHeight = obj.height(),
                    modalWidth = obj.width(),
                    yOffset = (viewPortHeight - modalHeight) / 2,
                    xOffset = (viewPortWidth - modalWidth) / 2;

                obj.css('top', yOffset + 'px');
                obj.css('left', xOffset + 'px');
                obj.css('display', 'block');
            }

            function modal_close (obj) {
                obj.hide();
            }

            function overlay_open () {
                $('.overlay').show();
            }

            function overlay_close () {
                $('.overlay').hide();
            }
            
            function open_folder (obj) {
                var path = obj.data('path');

                if (Object.keys(paths).indexOf(path) > -1) {
                    openFolderToDisplay (obj, paths[path])
                } else {
                    request_folder(obj, path);
                }

                $('#path').val(path);
            }

            function request_folder (obj, path) {
                $.ajax({
                    'method'        : 'POST',
                    'url'           : '/dropbox/folder.php',
                    'dataType'      : 'JSON',
                    'data'          : {
                        'action'    : 'open',
                        'path'      : path
                    },
                    'success'       : function (data) {
                        if (data.success) {
                            $('.folder.active').removeClass('active');
                            obj.addClass('active');

                            updateDisplays(obj, data);
                        }
                    }
                });
            }

            function updateDisplays (obj, data) {
                paths[path] = data.data.entries;
                addSubFolderToTree(obj, data.data.entries);
                openFolderToDisplay (obj, data.data.entries)
            }
            
            function addSubFolderToTree (obj, entries) {
                var folders = [],
                    files = [];

                for (var i in entries) {
                    if (entries.hasOwnProperty(i)) {
                        var entry = entries[i];

                        if (entry['.tag'] == 'folder') {
                            folders.push('<li><a href="#" class="folder" data-path="' + entry.path_lower + '">' + entry.name + '</a></li>');
                        } else {
                            files.push('<li><a href="#">' + entry.name + '</a></li>');
                        }
                    }
                }

                if (obj.next('.tree-structure').length) {
                    obj.next('.tree-structure').remove();
                }

                obj.after('<ul class="tree-structure">' + folders.join('') + files.join('') + '</ul>');
            }
            
            function openFolderToDisplay (obj, entries) {
                var folderDisplay = $('.folder-display');

                folderDisplay.empty();

                var folders = [],
                    files = [];

                for (var i in entries) {
                    if (entries.hasOwnProperty(i)) {
                        var entry = entries[i];

                        if (entry['.tag'] == 'folder') {
                            folders.push(buildFolderDisplayItem(entry));
                        } else {
                            files.push(buildFolderDisplayItem(entry));
                        }
                    }
                }

                folderDisplay.append(folders.join('') + files.join(''));
            }

            function buildFolderDisplayItem (entry) {
                var item = '<li class="folder-display-element">';
                item += '<div class="folder-display-element-container">';


                if (entry['.tag'] != 'folder') {
                    item += '<div class="folder-display-element-overlay">';
                    item += '<div class="folder-display-element-overlay-anchor"><div class="folder-display-element-overlay-button-holder">';
                    item += '<a class="folder-display-element-overlay-button download" href="#" title="download" data-name="' + entry.name + '"><img src="public\\img\\download.png"></a>';
                    item += '<a class="folder-display-element-overlay-button delete" href="#" title="delete"><img src="public\\img\\delete.png"></a>';
                    item += '</div></div>';
                    item += '</div>';
                }

                item += '<a class="folder-display-element-item ' + (entry['.tag'] == 'folder' ? 'folder-trigger' : '') + '" href="#" title="' + entry.name + '" data-path="' + entry['path_lower'] + '">';
                item += '<div class="folder-display-element-icon"><img src="public\\img\\placeholder.png" alt=""></div>';
                item += '<div class="folder-display-element-name"><div class="folder-display-element-name-anchor"><span>' + entry.name + '</span></div></div>';
                item += '</a>';
                item += '</div>';
                item += '</li>';

                return item;
            }

        });
    </script>
    <script>
        function closeFrame (name) {
            $('#download-' + name).remove();
        }
    </script>
</body>
</html>