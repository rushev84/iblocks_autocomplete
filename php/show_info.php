<?php
require_once 'ini.php';
require_once 'MyIBlock.php';

$id = $_POST['id'];

$iBlock = new MyIBlock($id);

$properties = $iBlock->getProperties();
?>

<p><b>Поля инфоблока</b></p>
<table class="add_table">
    <tr>
        <td class="add_td"><label for="ACTIVE">Активность</label></td>
        <td><input
                    type="checkbox"
                    class="field-checkbox"
                    id="ACTIVE"
                    value="newsletter" checked/></td>
    </tr>
    <tr>
        <td>
            <hr>
            <i>Сгенерировать случайные значения:</i>
        </td>
    </tr>
    <tr>
        <td class="add_td"><label for="NAME">Имя</label></td>
        <td><input
                    type="checkbox"
                    class="field-checkbox"
                    id="NAME"
                    value="newsletter" checked/></td>
    </tr>
    <tr>
        <td class="add_td"><label for="PREVIEW_TEXT">Предварительное описание</label></td>
        <td><input
                    type="checkbox"
                    class="field-checkbox"
                    id="PREVIEW_TEXT"
                    value="newsletter" checked/></td>
    </tr>
    <tr>
        <td class="add_td"><label for="DETAIL_TEXT">Детальное описание</label></td>
        <td><input
                    type="checkbox"
                    class="field-checkbox"
                    id="DETAIL_TEXT"
                    value="newsletter" checked/></td>
    </tr>

</table>


<p><b>Свойства инфоблока</b></p>
<table class="add_table">
    <tr>
        <td>
            <i>Сгенерировать случайные значения:</i>
        </td>
    </tr>
    <?php $i = 1; ?>
    <?php foreach ($properties as $key => $value): ?>
        <tr>
            <td class="add_td"><label for="<?= $key ?>"><?= $value['NAME'] ?></label></td>
            <td><input
                        type="checkbox"
                        class="property-checkbox"
                        id="<?= $value['CODE'] ?>"
                        value="newsletter" checked/></td>
        </tr>
        <?php $i++ ?>
    <?php endforeach; ?>
</table>
<br>
Количество элементов: <input id="count" type="number" style="width: 50px" value="3"><br><br>
<button class="btn" onclick="sendRequest()">Создать элементы</button>
<br>

<div class="resp"></div>

<script>

    // console.log(document.getElementById("INSTRUMENT_TYPE").checked)

    function sendRequest() {

        let request = {
            'IBLOCK_ID': <?= $id ?>,
            'COUNT': document.getElementById("count").value,
            'fields': [],
            'properties': []
        }

        fieldCheckboxes = document.querySelectorAll('.field-checkbox')

        fieldCheckboxes.forEach((fieldCheckbox) => {
            if (fieldCheckbox.checked) {
                request.fields.push(fieldCheckbox.id)
            }
        })

        propertyCheckboxes = document.querySelectorAll('.property-checkbox')

        propertyCheckboxes.forEach((propertyCheckbox) => {
            if (propertyCheckbox.checked) {
                request.properties.push(propertyCheckbox.id)
            }
        })

        // console.log(request)

        $
            .ajax({
                method: "POST",
                url: "php/add_elements.php",
                data: {
                    request: request
                }
            })
            .done(function (response) {
                $("div.resp").html(response);
            })
    }

</script>