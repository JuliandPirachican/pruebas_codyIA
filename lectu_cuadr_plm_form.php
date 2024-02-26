<?php require 'components/header/header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LECTURA CUADRICULA PLM</title>
    <link rel="stylesheet" href="assets/css/lectu_cuadr_plm.css">
    
    <script src="assets/js/lectu_cuadr_plm.js"></script>
</head>
<body>
    <div class="container">
        <br>
        <div class="card">
        <!-- action="lectu_cuadr_plm_controller.php" method="post" -->
            <form  enctype="multipart/form-data" id="carg_cuadri"  method="post">
                <br>
                <br>
                <div class="row">
                    <div class="col-md-6 form-group" >
                        <label class="label-control" for="camp_ad">Campaña Advance<small id="indicativo">*</small></label>
                        <input class="form-control" type="text" name="camp_ad" id="camp_ad" required>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="label-control" for="camp_nal">Campaña Nacional<small id="indicativo">*</small></label>
                        <input class="form-control" type="text" name="camp_nal" id="camp_nal" required>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="label-control" for="col_ini">Columna Inicial<small id="indicativo">*</small></label>
                        <input class="form-control" type="text" name="col_ini" id="col_ini" required>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="label-control" for="carg_arch">Cargar Cuadricula<small id="indicativo">*</small></label>
                        <input class="form-control" type="file" name="carg_arch" id="carg_arch" required accept=".xlsx,.xls">
                        <br>
                        <small class="label-control" id="adv">*La columna inicial debe ser igual en  todas las cuadriculas para evitar  errores*</small>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 form-group">
                        <small class="label-control" >Campos Obligatorios Marcados con *</small>

                    </div>
                </div>
                <br>
                <br>
                <div class="row" id="divbtn">
                    <div class="col-md-12 form-group"> 
                        <button class="btn btn-lg btn-success" id="send" type="submit"><i class="fa-solid fa-floppy-disk"></i> Cargar Informacion</button>
                    </div>
                </div>
                <br>
                <br>
            </form>
        </div>
    </body>
    </div>
</html>