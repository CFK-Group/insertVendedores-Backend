<?php
 //var_dump($renta)
$fmt = new NumberFormatter('America/Santiago	', NumberFormatter::CURRENCY);
?>
<div class="container">
    <table class="table table-striped">
        <thead>
        <tr>
            <th>Nombre</th>
            <th>Tango</th>
            <th>Renta</th>
            <th>Comision Por Ventas</th>
            <th>Bono Concurso</th>
            <th>Bono Permanencia</th>
            <th>Error</th>
        </tr>
        </thead>
        <tbody>
        <?php
        for ($i = 0; $i < count($renta); $i++) {
            ?>
            <tr>
                <td><?= $renta[$i]['nombre'] ?></td>
                <td><?= $renta[$i]['tango'] ?></td>
                <td><?= $fmt->formatCurrency($renta[$i]['renta'], 'CLP')?></td>
                <td><?= $fmt->formatCurrency($renta[$i]['comisionVenta'], 'CLP')?></td>
                <td><?= $fmt->formatCurrency($renta[$i]['bonoConcurso'], 'CLP')?></td>
                <td><?= $fmt->formatCurrency($renta[$i]['bonoPermanencia'], 'CLP')?></td>
                <td><?= $fmt->formatCurrency($renta[$i]['error'], 'CLP')?></td>
            </tr>
            <?php

        }
        ?>
        </tbody>
    </table>
</div>
