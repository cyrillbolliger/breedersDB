<table class="sortable_element deletable_element">
    <tr>
        <td class="single-icon sortable_handle">
            <i class="fa fa-arrows-v" aria-hidden="true"></i>
        </td>
        <td>
            <?= $this->element('Mark/field', ['markFormProperty' => $markFormProperty, 'required' => false]); ?>
        </td>
        <td class="single-icon delete_button">
            <i class="fa fa-trash" aria-hidden="true"></i>
        </td>
    </tr>
</table>
