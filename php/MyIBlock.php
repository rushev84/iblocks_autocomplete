<?php

class MyIBlock
{
    private int $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    // МЕТОДЫ, ВОЗВРАЩАЮЩИЕ СЛУЧАЙНЫЕ ДАННЫЕ

    // Возвращает случайное слово из массива слов.
    public static function getRandWord()
    {
        $words = ['rand_word1', 'rand_word2', 'rand_word3', 'rand_word4', 'rand_word5'];
        return $words[rand(0, count($words) - 1)];
    }

    public static function getRandNumber()
    {
        return rand(1, 1000);
    }

    // Принимает код свойства инфоблока (свойство должно быть типа "список").
    // Возвращает случайный элемент из списка для данного свойства.
    public function getRandListItem(string $propertyCode): array
    {
        $property_enums = CIBlockPropertyEnum::GetList(array("DEF" => "DESC", "SORT" => "ASC"), array("IBLOCK_ID" => $this->id, "CODE" => $propertyCode));

        $enums = [];

        while ($enum_fields = $property_enums->GetNext()) {
            $enums[] = $enum_fields;
        }

        $enumIds = array_map(fn($enum) => $enum['ID'], $enums);

        $randEnum = $enumIds[array_rand($enumIds)];

        return ["VALUE" => $randEnum];
    }

    // Возвращает случайный элемент данного инфоблока.
    public function getRandElement(): array
    {
        $elems = $this->getAllElements();

        $elemsIds = array_map(fn($elem) => $elem['ID'], $elems);

        $randElemId = $elemsIds[array_rand($elemsIds)];

        return ["VALUE" => $randElemId];
    }


    // МЕТОДЫ, РАБОТАЮЩИЕ С ИНФОБЛОКАМИ, СВОЙСТВАМИ И Т.Д.

    // Возвращает все инфоблоки данного сайта.
    public static function getAll(): array
    {
        $res = \CIBlock::GetList(
            array(
                "NAME" => "ASC"
            ),
            array(
                "ACTIVE" => "Y"
            ), true
        );

        $iblocks = [];

        while ($ar_res = $res->Fetch()) {
            $iblocks[] = [
                'id' => $ar_res['ID'],
                'name' => $ar_res['NAME']
            ];
        }

        return $iblocks;
    }

    // TODO: В методах getRandListItem и getRandElement не совсем корректны возвращаемые значения. Подумать, как исправить.

    // Возвращает массив описаний свойств данного инфоблока (id, имя, код, тип).
    public function getProperties(): array
    {
        $properties = CIBlockProperty::GetList(array("sort" => "asc", "name" => "asc"), array("ACTIVE" => "Y", "IBLOCK_ID" => $this->id));

        $props = [];

        while ($prop_fields = $properties->GetNext()) {
            $props[] = $prop_fields;
        }

        return array_map(fn($property) => array_filter($property, fn($key) => $key === 'ID' || $key === 'NAME' || $key === 'PROPERTY_TYPE' || $key === 'CODE', ARRAY_FILTER_USE_KEY),
            $props);
    }

    // Принимает запрос, полученный со страницы выбора свойств.
    // Возвращает свойства инфоблока, отфильтрованные в соответствии с запросом.
    public function getOnlyChosenProperties(array $request): array
    {
        $allProperties = $this->getProperties();
        return array_filter($allProperties, fn($property) => in_array($property['CODE'], $request['properties']));
    }

    // Принимает свойство данного инфоблока.
    // Возвращает значение свойства, сгенерированное случайным образом.
    public function getPropertyValue(array $property)
    {
        /* PROPERTY_TYPE - тип свойства:
            S - строка (ГОТОВО)
            N - число (ГОТОВО)
            L - список (ГОТОВО)
            F - файл
            G - привязка к разделу
            E - привязка к элементу (ГОТОВО)
        */
        switch ($property['PROPERTY_TYPE']) {
            case 'S':
                return $this->getRandWord();
            case 'N':
                return $this->getRandNumber();
            case 'L':
                return $this->getRandListItem($property['CODE']);
            case 'E':
                return $this->getRandElement();

            default:
                return 'other type';
        }
    }

    // Принимает запрос со страницы.
    // Ничего не возвращает. Печатает id добавленных элементов (в случае успеха) или сообщения об ошибках (в случае провала).
    public function addElements(array $request)
    {
        global $USER;

        $chosenProperties = $this->getOnlyChosenProperties($request);

        // добавляем элементы
        for ($i = 1; $i <= $request['COUNT']; $i++) {
            $randNumber = rand();

            $el = new CIBlockElement;

            // общий массив для полей и свойств
            $arLoadProductArray = array(
                "IBLOCK_ID" => $request['IBLOCK_ID'],
                "NAME" => $this->getRandWord(),
                "CODE" => "code_{$randNumber}",

                "MODIFIED_BY" => $USER->GetID(), // элемент изменен текущим пользователем
                "IBLOCK_SECTION_ID" => false,          // элемент лежит в корне раздела
            );


            // добавляем поля
            in_array('ACTIVE', $request['fields']) ? $arLoadProductArray['ACTIVE'] = 'Y' : $arLoadProductArray['ACTIVE'] = 'N';

            if (in_array('PREVIEW_TEXT', $request['fields'])) {
                $arLoadProductArray['PREVIEW_TEXT'] = $this->getRandWord();
//        dd($generator->getRandWord());
            }

            if (in_array('DETAIL_TEXT', $request['fields'])) {
                $arLoadProductArray['DETAIL_TEXT'] = $this->getRandWord();
            }

            // создаём свойства для $arLoadProductArray
            $PROP = [];

            foreach ($chosenProperties as $property) {
                $PROP[$property['CODE']] = $this->getPropertyValue($property);
            }

//            dd($PROP);

            // добавляем свойства в $arLoadProductArray (если они есть)
            if (!empty($PROP)) {
                $arLoadProductArray['PROPERTY_VALUES'] = $PROP;
            }

            $PRODUCT_ID = $el->Add($arLoadProductArray);

            if ($PRODUCT_ID) {
                echo "Создан новый элемент с ID = " . $PRODUCT_ID;
                echo "<br>";
            } else {
                echo "Error: " . $el->LAST_ERROR;
            }
        }
    }

    public function getAllElements()
    {
        $arSelect = array("ID", "NAME", "DATE_ACTIVE_FROM");
        $arFilter = array("IBLOCK_ID" => IntVal($this->id));
        $res = CIBlockElement::GetList(array(), $arFilter, false, array("nPageSize" => 50), $arSelect);

        $elems = [];

        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $elems[] = $arFields;
        }

        return $elems;
    }
}