<?php
if ( $_REQUEST['values'] && $_POST['values'] && AllowEdit())
{
	foreach ( (array) $_REQUEST['values'] as $id => $columns)
	{
//FJ fix SQL bug invalid sort order
		if (empty($columns['SORT_ORDER']) || is_numeric($columns['SORT_ORDER']))
		{
			if ( $id!='new')
			{
				$sql = "UPDATE SCHOOL_GRADELEVELS SET ";

				foreach ( (array) $columns as $column => $value)
				{
					$sql .= $column."='".$value."',";
				}
				$sql = mb_substr($sql,0,-1) . " WHERE ID='".$id."'";
				DBQuery($sql);
			}
			else
			{
				$sql = "INSERT INTO SCHOOL_GRADELEVELS ";

				$fields = 'ID,SCHOOL_ID,';
				$values = db_seq_nextval('SCHOOL_GRADELEVELS_SEQ').",'".UserSchool()."',";

				$go = 0;
				foreach ( (array) $columns as $column => $value)
				{
					if ( !empty($value) || $value=='0')
					{
						$fields .= $column.',';
						$values .= "'".$value."',";
						$go = true;
					}
				}
				$sql .= '(' . mb_substr($fields,0,-1) . ') values(' . mb_substr($values,0,-1) . ')';

				if ( $go)
					DBQuery($sql);
			}
		}
		else
			$error[] = _('Please enter a valid Sort Order.');
	}
}

DrawHeader(ProgramTitle());

if ( $_REQUEST['modfunc'] === 'remove' && AllowEdit() )
{
	if ( DeletePrompt( _( 'Grade Level' ) ) )
	{
		DBQuery("DELETE FROM SCHOOL_GRADELEVELS WHERE ID='" . $_REQUEST['id'] . "'");

		// Unset modfunc & ID.
		$_REQUEST['modfunc'] = false;
		$_SESSION['_REQUEST_vars']['modfunc'] = false;
		$_SESSION['_REQUEST_vars']['id'] = false;
	}
}

// FJ fix SQL bug invalid sort order
echo ErrorMessage( $error );

if ( $_REQUEST['modfunc']!='remove')
{
	$sql = "SELECT ID,TITLE,SHORT_NAME,SORT_ORDER,NEXT_GRADE_ID FROM SCHOOL_GRADELEVELS WHERE SCHOOL_ID='".UserSchool()."' ORDER BY SORT_ORDER";
	$QI = DBQuery($sql);
	$grades_RET = DBGet($QI,array('TITLE' => 'makeTextInput','SHORT_NAME' => 'makeTextInput','SORT_ORDER' => 'makeTextInput','NEXT_GRADE_ID' => 'makeGradeInput'));

	$columns = array('TITLE' => _('Title'),'SHORT_NAME' => _('Short Name'),'SORT_ORDER' => _('Sort Order'),'NEXT_GRADE_ID' => _('Next Grade'));
	$link['add']['html'] = array('TITLE'=>makeTextInput('','TITLE'),'SHORT_NAME'=>makeTextInput('','SHORT_NAME'),'SORT_ORDER'=>makeTextInput('','SORT_ORDER'),'NEXT_GRADE_ID'=>makeGradeInput('','NEXT_GRADE_ID'));
	$link['remove']['link'] = 'Modules.php?modname='.$_REQUEST['modname'].'&modfunc=remove';
	$link['remove']['variables'] = array('id' => 'ID');

	echo '<form action="Modules.php?modname='.$_REQUEST['modname'].'&modfunc=update" method="POST">';
	DrawHeader('',SubmitButton(_('Save')));

	ListOutput($grades_RET,$columns,'Grade Level','Grade Levels',$link);
	echo '<div class="center">' . SubmitButton( _( 'Save' ) ) . '</div>';
	echo '</form>';
}

function makeTextInput($value,$name)
{	global $THIS_RET;

	if ( $THIS_RET['ID'])
		$id = $THIS_RET['ID'];
	else
		$id = 'new';

	if ( $name!='TITLE')
		$extra = 'size=5 maxlength=2';
	if ( $name=='SORT_ORDER')
		$comment = '<!-- '.$value.' -->';

	return $comment.TextInput($value,'values['.$id.']['.$name.']','',$extra);
}

function makeGradeInput($value,$name)
{	global $THIS_RET,$grades;

	if ( $THIS_RET['ID'])
		$id = $THIS_RET['ID'];
	else
		$id = 'new';

	if ( ! $grades)
	{
		$grades_RET = DBGet(DBQuery("SELECT ID,TITLE FROM SCHOOL_GRADELEVELS WHERE SCHOOL_ID='".UserSchool()."' ORDER BY SORT_ORDER"));
		if (count($grades_RET))
		{
			foreach ( (array) $grades_RET as $grade)
				$grades[$grade['ID']] = $grade['TITLE'];
		}
	}

	return SelectInput($value,'values['.$id.']['.$name.']','',$grades,_('N/A'));
}
