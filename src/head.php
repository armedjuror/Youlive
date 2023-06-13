<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?=APP_NAME?></title>

    <!-- Custom fonts for this template-->
    <link href="ui/extras/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <link href="ui/extras/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
    <link href="ui/css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        .loader {
            top: 0;
            left: 0;
            position: fixed;
            z-index: 10000000;
            background: rgba(0,0,0,0.8);
            height: 100%;
            width: 100%;
            margin: auto;
        }
        #AjaxLoaderMessage{
            margin: 5vh auto;
            width: 300px;
            height: 500px;
            max-width: 90%;
            text-align: center;
        }
        #AjaxLoaderMessage p{
            font-size: 20px;
            color: #FFF;
        }
        #cover-spin {
            position:fixed;
            width:100%;
            left:0;right:0;top:0;bottom:0;
            z-index:9999;
        }

        @-webkit-keyframes spin {
            from {-webkit-transform:rotate(0deg);}
            to {-webkit-transform:rotate(360deg);}
        }

        @keyframes spin {
            from {transform:rotate(0deg);}
            to {transform:rotate(360deg);}
        }

        #cover-spin::after {
            content:'';
            display:block;
            position:absolute;
            left:48%;top:40%;
            width:40px;height:40px;
            border-style:solid;
            border-color:white;
            border-top-color:transparent;
            border-width: 4px;
            border-radius:50%;
            -webkit-animation: spin .8s linear infinite;
            animation: spin .8s linear infinite;
        }

    </style>
</head>

<body id="page-top">
<!--<div class="loader" id="AjaxLoader" style="display: none">-->
<div class="loader" id="AjaxLoader" >
    <div id="AjaxLoaderMessage" class="mt-4">
    </div>
    <div id="cover-spin"></div>
</div>


<!-- Page Wrapper -->
<div id="wrapper">