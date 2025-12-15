<?php 

$string = "<!DOCTYPE html>
<html>
    <head>
        <meta charset=\"utf-8\">
        <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
        <title>Cetak Data " . $c . "</title>
        <link rel=\"icon\" href=\"<?= icon() ?>\" type=\"image/jpeg\">
        <style>
            .tg  {border-collapse:collapse;border-spacing:0;width: 100%}
            .tg td{font-family:Arial, sans-serif;font-size:14px;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:black;}
            .tg th{font-family:Arial, sans-serif;font-size:14px;font-weight:normal;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:black;}
            .tg .tg-lboi{border-color:inherit;text-align:left;vertical-align:middle}
            .tg .tg-9wq8{border-color:inherit;text-align:center;vertical-align:middle}
            .tg .tg-uzvj{font-weight:bold;border-color:inherit;text-align:center;vertical-align:middle}
            .landing{
                width: 50%;
                margin: 25px auto;
                text-align: center;
                border: 5px solid black;
                border-radius: 20px;
                padding: 20px;
            }

            .landing button{
                color: white;
                width: 30%;
                height: 50px;
                margin-top: 20px;
                background-color: green;
                font-size: 15pt;
                border-radius: 0 20px 0 20px;
                transition: 0.5s;
            }

            .landing button:hover{
                border-radius: 0 20px 0 20px;
                transition: 0.5s;
            }
            #show {
                display: block;
            }

            #hidden {
                display: none;
            }
            @media print {
                #show {
                    display: none;
                }
                #hidden {
                    display: block;
                }
                @page {
                    size: 'A4';
                    size: portrait;
                }
            }
        </style>
    </head>
    <body onload=\"setTimeout(cetak,1000)\">
        <div id=\"show\">
            <div class=\"landing\">
                <img src=\"<?= icon() ?>\" width=\"50%\" style=\"padding-top: -20px\" class=\"center\"><br>
                <button onclick=\"window.print()\" style=\"width: 100%\">Cetak Data Admin</button><br>
                <a href=\"<?= site_url('dashboard')?>\"><button style=\"background: red\">Dashboard</button></a>
            </div>
        </div>
        <div id=\"hidden\">
            <h2 style=\"text-align: center\">Data " . $c . "</h2>
            <table class=\"tg\">
                <tr>
                    <th class=\"tg-uzvj\" width=\"5%\">No</th>";
foreach ($non_pk as $row) {
    $string .= "\n\t\t\t\t\t<th class=\"tg-uzvj\">" . label($row['column_name']) . "</th>";
}
$string .= "\n\t\t\t\t</tr>";
$string .= "\n\t\t\t\t<?php \$no = 1; foreach (\$data_" . $table_name . " as \$value) : ?>
                    <tr>";
$string .= "\n\t\t\t\t\t\t<td class=\"tg-9wq8\"><?= \$no++ ?></td>";

foreach ($non_pk as $row) {
    $string .= "\n\t\t\t\t\t\t<td class=\"tg-lboi\"><?= \$value->". $row['column_name'] . " ?></td>";
}

$string .=  "\t
                    </tr>
                <?php endforeach ?>
            </table>
        </div>
        <script type=\"text/javascript\">
            function cetak(){
                print();
            }
        </script>
    </body>
</html>";


$hasil_view_pdf = createFile($string, $target."views/" . $c_url . "/" . $v_pdf_file);

?>