<?php
if (GROUP_ID != SUPERADMIN_USER) {
    $general->redirect(URL);
}
$tPTbl = 1;
$addBtn = '<a href="' . $pUrl . '&add" class="btn btn-success btn-sm">Add Module</a><a href="' . $pUrl . '&order" class="btn btn-danger btn-sm">Short</a>';
$general->pageHeader('Modules', array(), $addBtn);
if (isset($_GET['add'])) {
    if (isset($_POST['add'])) {
        $name = $_POST["name"];
        $slug = $_POST["slug"];
        $folder = $_POST["folder"];
        $pageneame = $_POST["pageneame"];
        $parent = intval($_POST["parent"]);
        if (empty($name)) {
            setMessage(5);
            $error = 1;
        }
        if (empty($slug)) {
            $slug = $general->make_url($name);
        }

        if ($parent != 0) {
            $pData = $db->get_rowData('module', 'id', $parent);
            if (!empty($pData)) {
                if ($pData['parent'] != 0) {
                    setMessage(50);
                    $error = 1;
                }
            } else {
                setMessage(11);
                $error = 1;
            }
        }
        if (!isset($error)) {
            $data = array(
                'name'   => $name,
                'parent'  => $parent,
                'slug'    => $slug,
                'folder'  => $folder,
                'page_name' => $pageneame
            );
            $insert = $db->insert('module', $data);
            if ($insert) {
                $general->redirect($pUrl, 29, 'Module');
            }
        }
    }
?>
    <h2 class="sub-header">Add module</h2>
    <?php show_msg(); ?>
    <div class="form-group col-6">
        <form method="post">
            <table class="table table-bordered table-striped">
                <tr>
                    <td>Name</td>
                    <td><input type="text" class="form-control" name="name" required="required" value=""></td>
                </tr>
                <tr>
                    <td>Slug</td>
                    <td><input type="text" class="form-control" name="slug" value=""></td>
                </tr>
                <tr>
                    <td>Folder</td>
                    <td><input type="text" class="form-control" name="folder" value=""></td>
                </tr>
                <tr>
                    <td>Pagename</td>
                    <td><input type="text" name="pageneame" class="form-control" value=""></td>
                </tr>
                <tr>
                    <td>Parent</td>
                    <td>
                        <select name="parent" class="select_box">
                            <option value="">Parent</option>
                            <?php
                            $sqlQuery = "where parent=0 order by name asc";
                            $mod   = $db->selectAll('module', $sqlQuery);
                            foreach ($mod as $mo) {
                            ?>
                                <option value="<?= $mo['id'] ?>"><?= $mo['name'] ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><input type="submit" name="add" value="Submit" class="btn btn-success"></td>
                </tr>
            </table>
        </form>
    </div>
<?php
} elseif (isset($_GET['order'])) {

    $id     = 'id';
    $title     = 'name';
    $mainArray = $db->selectAll('module', ' where parent = 0 order by sequence');
?>
    <style type="text/css">
        #contentWrap {
            width: 700px;
            margin: 0 auto;
            height: auto;
            overflow: hidden;
        }

        #contentTop {
            width: 600px;
            padding: 0px;
            margin-left: 30px;
        }

        #contentLeft {
            float: left;
            width: 100%;
        }

        #contentLeft li {
            background: url("images/left-meny.png") repeat-y scroll left top rgba(0, 0, 0, 0);
            border: 1px solid #000;
            color: #0E0E0E;
            list-style: none outside none;
            margin: 6px 0 4px;
            padding: 4px;
        }

        #contentLeft li:hover {
            cursor: move;
        }
    </style>
    <script type="text/javascript">
        $(document).ready(function() {
            $(function() {
                $("#contentLeft ul").sortable({
                    opacity: 0.6,
                    cursor: 'move',
                    update: function() {
                        var order = $(this).sortable("serialize"); // + '&hs_ord=ord&actn=<?= 1 ?>&trg=1'; 
                        var find = 'recordsArray';
                        var re = new RegExp(find, 'g');
                        dd = order.replace(re, '');
                        var find = "[[\]]=";
                        var re = new RegExp(find, 'g');
                        dd = dd.replace(re, '');
                        var find = "&";
                        var re = new RegExp(find, 'g');
                        dd = dd.replace(re, ',');
                        orderArray = dd;

                        $.post(ajUrl, {
                            moduleOrder: 0,
                            ord: orderArray
                        }, function(theResponse) {
                            t(theResponse);
                        });
                    }
                });
            });

        });
    </script>
    <div id="contentWrap">
        <div id="contentLeft">
            <ul>
                <?php
                $order = 'sequence';
                foreach ($mainArray as $sm) {
                    $childMenu = $db->selectAll('module', ' where parent = ' . $sm[$id] . ' order by ' . $order);
                ?>
                    <li style="max-height: 164px; overflow: auto;" id="recordsArray_<?= $sm[$id] ?>"><?= $sm[$order] . ") " . $sm[$title] ?>
                        <?php
                        if (!empty($childMenu)) {
                        ?>
                            <ul>
                                <?php
                                foreach ($childMenu as $cm) {
                                ?>
                                    <li id="recordsArray_<?= $cm[$id] ?>"><?= $cm[$order] . ") " . $cm[$title] ?></li>
                                <?php
                                }
                                ?>
                            </ul>
                        <?php
                        }
                        ?>

                    </li>
                <?php } ?>
            </ul>
        </div>

    </div>
<?php
} elseif (isset($_GET['edit'])) {
    $edit = intval($_GET['edit']);
    $m = $db->get_rowData('module', 'id', $edit);
    if (empty($m)) {
        $general->redirect($pUrl, array(37, 'module'));
    }
    if (isset($_POST['edit'])) {
        $name = $_POST["name"];
        $slug = $_POST["slug"];
        $folder = $_POST["folder"];
        $pageneame = $_POST["pageneame"];
        $textcolor = $_POST["textcolor"];
        $color = $_POST["color"];
        $icon = $_POST["icon"];
        $parent = intval($_POST["parent"]);
        if (isset($_POST["select"])) {
            $for_home = 1;
        } else {
            $for_home = 0;
        }
        if (empty($name)) {
            setMessage(5);
            $error = 1;
        }
        if (empty($slug)) {
            $slug = $general->make_url($name);
        }
        if ($parent != 0) {
            $pData = $db->get_rowData('module', 'id', $parent);
            if (!empty($pData)) {
                if ($pData['parent'] != 0) {
                    setMessage(50);
                    $error = 1;
                }
            } else {
                setMessage(51);
                $error = 1;
            }
        }
        if (!isset($error)) {
            $data = array(
                'name'   => $name,
                'parent'  => $parent,
                'slug'    => $slug,
                'folder'  => $folder,
                'page_name' => $pageneame,
                'for_home' => $for_home,
                'color' => $color,
                'text_color' => $textcolor,
                'icon' => $icon
            );
            $where = array('id' => $edit);
            $update = $db->update('module', $data, $where);
            if ($update) {
                $general->redirect($pUrl, 30, 'Module');
            }
        }
    }

?>
    <h2>Edit module</h2>
    <?php show_msg(); ?>
    <div class="form-group col-6">
        <form method="post">
            <table class="table table-bordered">
                <tr>
                    <td>Name</td>
                    <td><input type="text" name="name" class="form-control" required="required" value="<?= $m['name'] ?>"></td>
                </tr>
                <tr>
                    <td>Slug</td>
                    <td><input type="text" name="slug" class="form-control" value="<?= $m['slug'] ?>"></td>
                </tr>
                <tr>
                    <td>Folder</td>
                    <td><input type="text" name="folder" class="form-control" value="<?= $m['folder'] ?>"></td>
                </tr>
                <tr>
                    <td>Pagename</td>
                    <td><input type="text" name="pageneame" class="form-control" value="<?= $m['page_name'] ?>"></td>
                </tr>
                <tr>
                    <td>Parent</td>
                    <td>
                        <select name="parent" class="select2 form-control">
                            <option value="">Parent</option>
                            <?php
                            $sqlQuery = "where parent=0 order by name asc";
                            $mod   = $db->selectAll('module', $sqlQuery);
                            foreach ($mod as $mo) {
                            ?>
                                <option value="<?= $mo['id'] ?>" <?= $general->selected($mo['id'], $m['parent']) ?>><?= $mo['name'] ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Icon</td>
                    <td>
                        <input type="text" name="icon" class="form-control" value="<?= $m['icon'] ?>"> <span><a target=" _target" href="https://themify.me/themify-icons">themify link</a></span>
                    </td>
                </tr>
                <tr>
                    <td>Text color</td>
                    <td>
                        <input class="<?php echo "jscolor {onFineChange:'update(this,\\'tc_" . $m['text_color'] . "\\')'"; ?>" value="<?php echo $m['text_color'] == '' ? '000000' : $m['text_color']; ?>" placeholder="<?php echo 'Text Color'; ?>" id="<?php echo 'pColor'; ?>" type="text" name="textcolor" <?php echo 'n' == 'y' ? 'required="required"' : ''; ?> <?php echo ''; ?>>
                    </td>
                </tr>
                <tr>
                    <td>Color</td>
                    <td>
                        <input class="<?php echo "jscolor {onFineChange:'update(this,\\'tc_" . $m['color'] . "\\')'"; ?>" value="<?php echo $m['color'] == '' ? 'FF1D09' : $m['color']; ?>" placeholder="<?php echo 'Color'; ?>" id="<?php echo 'pColor'; ?>" type="text" name="color" <?php echo 'n' == 'y' ? 'required="required"' : ''; ?> <?php echo ''; ?>>
                    </td>
                </tr>

                <tr>
                    <td>Add Home Page</td>
                    <td><input type="checkbox" name="select" class="checkbox checkbox-info checkbox-circle" <?php if ($m['for_home'] == 1) {
                                                                                                                echo "checked";
                                                                                                            } ?>></td>
                </tr>

                <tr>
                    <td>&nbsp;</td>
                    <td><input type="submit" name="edit" value="Submit" class="btn btn-success"></td>
                </tr>
            </table>
        </form>
    </div>
<?php
} else {
    $sqlQuery = " where parent=0 order by sequence asc";
    $mod   = $db->selectAll('module', $sqlQuery);
?>
    <?php show_msg(); ?>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Parent</th>
                <th>Slug</th>
                <th>Folder</th>
                <th>Pagename</th>
                <th>Edit</th>
                <th>Status</th>
            </tr>
        </thead>
        <?php
        foreach ($mod as $m) {
            $sqlQuery = " where parent=" . $m['id'] . " order by sequence asc";
            $smod   = $db->selectAll('module', $sqlQuery);
        ?>
            <tr>
                <td><b><?= $m['id'] ?></b></td>
                <td><?= $m['name'] ?></td>
                <td>-</td>
                <td><?= $m['slug'] ?></td>
                <td><?= $m['folder'] ?></td>
                <td><?= $m['page_name'] ?></td>
                <td>
                    <a href="<?= $pUrl ?>&edit=<?= $m['id'] ?>" class="btn btn-info">
                        Edit
                    </a>
                </td>
                <td><?php $general->onclickChangeBTN($m['id'], $general->checked($m['isActive'])); ?></td>
            </tr>
            <?php
            if (!empty($smod)) {
                foreach ($smod as $sm) {
            ?>
                    <tr>
                        <td><?= $sm['id'] ?></td>
                        <td><?= $sm['name'] ?></td>
                        <td><?= $m['name'] ?></td>
                        <td><?= $sm['slug'] ?></td>
                        <td><?= $sm['folder'] ?></td>
                        <td><?= $sm['page_name'] ?></td>
                        <td>
                            <a href="<?= $pUrl ?>&edit=<?= $sm['id'] ?>" class="btn btn-info">Edit</a>
                        </td>
                        <td><?php $general->onclickChangeBTN($sm['id'], $general->checked($sm['isActive'])); ?></td>
                    </tr>
        <?php
                }
            }
        }
        ?>
    </table>
<?php
    $general->onclickChangeJavaScript('module', 'id');
}
?>