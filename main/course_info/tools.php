<?php
/* For licensing terms, see /license.txt */

require_once __DIR__.'/../inc/global.inc.php';

// The section for the tabs
$this_section = SECTION_COURSES;

$sessionId = api_get_session_id();

if (!empty($sessionId)) {
    api_not_allowed();
}

api_protect_course_script(true);

if (!api_is_allowed_to_edit()) {
    api_not_allowed(true);
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : '';

$toolName = get_lang('CustomizeIcons');

switch ($action) {
    case 'delete_icon':
        $tool = CourseHome::getTool($id);
        if (empty($tool)) {
            api_not_allowed(true);
        }

        $currentUrl = api_get_self().'?'.api_get_cidreq();
        Display::addFlash(Display::return_message(get_lang('Updated')));
        CourseHome::deleteIcon($id);
        header('Location: '.$currentUrl);
        exit;

        break;
    case 'edit_icon':
        $tool = CourseHome::getTool($id);
        if (empty($tool)) {
            api_not_allowed(true);
        }

        $interbreadcrumb[] = array(
            'url' => api_get_self().'?'.api_get_cidreq(),
            'name' => get_lang('CustomizeIcons'),
        );
        $toolName = Security::remove_XSS(stripslashes($tool['name']));

        $currentUrl = api_get_self().'?action=edit_icon&id='.$id.'&'.api_get_cidreq();

        $form = new FormValidator('icon_edit', 'post', $currentUrl);
        $form->addHeader(get_lang('EditIcon'));
        $form->addHtml('<div class="col-md-7">');
        $form->addText('name', get_lang('Name'));
        $form->addText('link', get_lang('Links'));
        $allowed_picture_types = array('jpg', 'jpeg', 'png');
        $form->addFile('icon', get_lang('CustomIcon'));
        $form->addRule('icon', get_lang('OnlyImagesAllowed').' ('.implode(',', $allowed_picture_types).')', 'filetype', $allowed_picture_types);
        $form->addSelect(
            'target',
            get_lang('LinkTarget'),
            [
                '_self' => get_lang('LinkOpenSelf'),
                '_blank' => get_lang('LinkOpenBlank')
            ]
        );
        $form->addSelect(
            'visibility',
            get_lang('Visibility'),
            array(1 => get_lang('Visible'), 0 => get_lang('Invisible'))
        );
        $form->addTextarea(
            'description',
            get_lang('Description'),
            array('rows' => '3', 'cols' => '40')
        );
        // Reset link button
        $toolInfo = array(
            array('tool_id' => '1','name' => 'course_description','link' => 'course_description/'),
            array('tool_id' => '2','name' => 'calendar_event','link' => 'calendar/agenda.php'),
            array('tool_id' => '3','name' => 'document','link' => 'document/document.php'),
            array('tool_id' => '4','name' => 'learnpath','link' => 'lp/lp_controller.php'),
            array('tool_id' => '5','name' => 'link','link' => 'link/link.php'),
            array('tool_id' => '6','name' => 'quiz','link' => 'exercise/exercise.php'),
            array('tool_id' => '7','name' => 'announcement','link' => 'announcements/announcements.php'),
            array('tool_id' => '8','name' => 'forum','link' => 'forum/index.php'),
            array('tool_id' => '9','name' => 'dropbox','link' => 'dropbox/index.php'),
            array('tool_id' => '10','name' => 'user','link' => 'user/user.php'),
            array('tool_id' => '11','name' => 'group','link' => 'group/group.php'),
            array('tool_id' => '12','name' => 'chat','link' => 'chat/chat.php'),
            array('tool_id' => '13','name' => 'student_publication','link' => 'work/work.php'),
            array('tool_id' => '14','name' => 'survey','link' => 'survey/survey_list.php'),
            array('tool_id' => '15','name' => 'wiki','link' => 'wiki/index.php'),
            array('tool_id' => '16','name' => 'gradebook','link' => 'gradebook/index.php'),
            array('tool_id' => '17','name' => 'glossary','link' => 'glossary/index.php'),
            array('tool_id' => '18','name' => 'notebook','link' => 'notebook/index.php'),
            array('tool_id' => '19','name' => 'attendance','link' => 'attendance/index.php'),
            array('tool_id' => '20','name' => 'course_progress','link' => 'course_progress/index.php'),
            array('tool_id' => '23','name' => 'search','link' => 'search'),
            array('tool_id' => '24','name' => 'blog_management','link' => 'blog/blog_admin.php'),
            array('tool_id' => '25','name' => 'tracking','link' => 'tracking/courseLog.php'),
            array('tool_id' => '26','name' => 'course_setting','link' => 'course_info/infocours.php'),
            array('tool_id' => '27','name' => 'course_maintenance','link' => 'course_info/maintenance.php'),
            array('tool_id' => '28','name' => 'bbb','link' => 'bbb/start.php')
        );
        foreach ($toolInfo as $ti) {
            if ($ti['tool_id'] == $tool['id']) {
                $t = $ti;
            }
        }
        $form->addButtonReset(get_lang('Restore'));
        $form->addHtml('<script>');
        $form->addHtml('$(document).ready(function() {
                            $("#icon_edit_reset").on("click", function() {
                                $("#icon_edit_name").attr("value", "' . $t['name'] . '");
                                $("#icon_edit_link").attr("value", "' . $t['link'] . '");
                            });
                        });');
        $form->addHtml('</script>');
        
        $form->addButtonUpdate(get_lang('Update'));
        $form->addHtml('</div>');
        $form->addHtml('<div class="col-md-5">');
        if (isset($tool['custom_icon']) && !empty($tool['custom_icon'])) {
            $form->addLabel(
                get_lang('CurrentIcon'),
                Display::img(
                    CourseHome::getCustomWebIconPath().$tool['custom_icon']
                )
            );

            $form->addCheckBox('delete_icon', null, get_lang('DeletePicture'));
        }
        $form->addHtml('</div>');
        $form->setDefaults($tool);
        $content = $form->returnForm();

        if ($form->validate()) {
            $data = $form->getSubmitValues();
            CourseHome::updateTool($id, $data);
            Display::addFlash(Display::return_message(get_lang('Updated')));
            if (isset($data['delete_icon'])) {
                CourseHome::deleteIcon($id);
            }
            $currentUrlReturn = api_get_self().'?'.api_get_cidreq();
            header('Location: '.$currentUrlReturn);
            exit;
        }
        break;
    case 'list':
    default:
        $toolList = CourseHome::toolsIconsAction(
            api_get_course_int_id(),
            api_get_session_id()
        );
        $iconsTools = '<div id="custom-icons">';
        $iconsTools .= Display::page_header(get_lang('CustomizeIcons'), null, 'h4');
        $iconsTools .= '<div class="row">';
        foreach ($toolList as $tool) {
            $tool['name'] = Security::remove_XSS(stripslashes($tool['name']));
            $toolIconName = 'Tool'.api_underscore_to_camel_case($tool['name']);
            $toolIconName = isset($$toolIconName) ? get_lang($toolIconName) : $tool['name'];

            $iconsTools .= '<div class="col-md-2">';
            $iconsTools .= '<div class="items-tools">';

            if (!empty($tool['custom_icon'])) {
                $image = CourseHome::getCustomWebIconPath().$tool['custom_icon'];
                $icon = Display::img($image, $toolIconName);
            } else {
                $image = (substr($tool['image'], 0, strpos($tool['image'], '.'))).'.png';
                $icon = Display::return_icon(
                    $image,
                    $toolIconName,
                    array('id' => 'tool_'.$tool['id']),
                    ICON_SIZE_BIG,
                    false
                );
            }

            $delete = (!empty($tool['custom_icon'])) ? "<a class=\"btn btn-default\" onclick=\"javascript:
                if(!confirm('".addslashes(api_htmlentities(get_lang('ConfirmYourChoice'), ENT_QUOTES, $charset)).
                "')) return false;\" href=\"".api_get_self().'?action=delete_icon&id='.$tool['iid'].'&'.api_get_cidreq()."\">
            <em class=\"fa fa-trash-o\"></em></a>" : "";
            $edit = '<a class="btn btn-default" href="'.api_get_self().'?action=edit_icon&id='.$tool['iid'].'&'.api_get_cidreq().'"><em class="fa fa-pencil"></em></a>';

            $iconsTools .= '<div class="icon-tools">'.$icon.'</div>';
            $iconsTools .= '<div class="name-tools">'.$toolIconName.'</div>';
            $iconsTools .= '<div class="toolbar">'.$edit.$delete.'</div>';
            $iconsTools .= '</div>';
            $iconsTools .= '</div>';
        }
        $iconsTools .= '</div>';
        $iconsTools .= '</div>';
        $content = $iconsTools;
        break;
}

$tpl = new Template($toolName);
$tpl->assign('content', $content);
$template = $tpl->get_template('layout/layout_1_col.tpl');
$tpl->display($template);
