function perfil(){

    if (document.getElementById("usuario-perfil").style.display == "flex") {
        
        document.getElementById("usuario-perfil").style.display = "none";
    }
    else if(document.getElementById("usuario-perfil").style.display == "none"){

        document.getElementById("usuario-perfil").style.display = "flex";
    }  
}

function buttom_post_over(){
    if (document.getElementById("text_hover").style.width == "0px") {
        
        document.getElementById("text_hover").style.width = "245px";
        document.getElementById("buttom_hover").style.borderTopRightRadius = "0%";
        document.getElementById("buttom_hover").style.borderBottomRightRadius = "0%";
        document.getElementById("text_hover_p").style.opacity = "1";
    }
}

function buttom_post_out(){
    if (document.getElementById("text_hover").style.width == "245px") {
        
        document.getElementById("text_hover").style.width = "0px";
        document.getElementById("buttom_hover").style.borderTopRightRadius = "100%";
        document.getElementById("buttom_hover").style.borderBottomRightRadius = "100%";
        document.getElementById("text_hover_p").style.opacity = "0";
    }
}
function reveal_post() {
    if (document.getElementById("div_create_tweets").style.display == "none") {
        
        document.getElementById("div_create_tweets").style.display = "block";
        document.getElementById("div_create_tweets").style.animation = "animation_height 1s, animation_opacity 2s";
    }
    else if (document.getElementById("div_create_tweets").style.display == "block") {
        
        document.getElementById("div_create_tweets").style.animation = "animation_height_close 1s, animation_opacity_close 1s";
        setTimeout(function() {
            document.getElementById("div_create_tweets").style.display = "none";
        }, 900);
    }
}

function img_tweet(){
    document.getElementById("erase_image").style.display = "flex";
    document.getElementById("img_prev").style.width = "100%";
    document.getElementById("img_prev").style.height = "178px";
    document.getElementById("img_prev").style.top = "0%";
    document.getElementById("img_prev").style.left = "0%";
    document.getElementById("img_prev").style.borderRadius = "10px";
    document.getElementById("fulAdjunto_tweet").style.width = "0px";
    document.getElementById("fulAdjunto_tweet").style.height = "0px";
}

function remove_image(){
    document.getElementById("img_prev").src = "./assets/header/img_icon.png";
    document.getElementById("img_prev").style.width = "50px";
    document.getElementById("img_prev").style.height = "50px";
    document.getElementById("img_prev").style.top = "31%";
    document.getElementById("img_prev").style.left = "43%";
    document.getElementById("erase_image").style.display = "none";
    document.getElementById("fulAdjunto_tweet").style.width = "100%";
    document.getElementById("fulAdjunto_tweet").style.height = "130px";
}

function ajax_chat(){

    var req = new XMLHttpRequest();

    req.onreadystatechange = function() {
        
        if (req.readyState == 4 && req.status == 200) {
                document.getElementById("div_messages_chat_background").innerHTML = req.responseText;
        }
    }

    req.open('GET', 'chat.php', true);
    req.send();
}
function overFlow(){

    $('#div_messages_chat_background').scrollTop( $('#div_messages_chat_background').prop('scrollHeight') );
}

function mostrarMensaje(event){

    var formulario = $('#form_send_message').serialize();
    var fulAdjunto = $("input[name=fulAdjunto]")[0].files[0];

    const mensaje = new FormData();
    mensaje.append('formulario', formulario);
    mensaje.append('fulAdjunto', fulAdjunto);    
    
    $.ajax({
        type: "POST",
        url: "InkMensajes.php",
        data: mensaje,
        contentType: false,
        processData: false,
        success: function(resp){

            var el = $("#fulAdjunto");
            var tx = $("#txtMensaje_Messages_crear");

            el.wrap("<form>").closest("form").get(0).reset();
            tx.wrap("<form>").closest("form").get(0).reset();
            el.unwrap();
            tx.unwrap();

            setTimeout(overFlow, 100);
        }
    });

    event.preventDefault();
    return false;
}

function previewImage(event, querySelector){

    //Recuperamos el input que desencadeno la acción
    const input = event.target;
    
    //Recuperamos la etiqueta img donde cargaremos la imagen
    $img_prev = document.querySelector(querySelector);

    // Verificamos si existe una imagen seleccionada
    if(!input.files.length) return
    
    //Recuperamos el archivo subido
    file = input.files[0];

    //Creamos la url
    objectURL = URL.createObjectURL(file);
    
    //Modificamos el atributo src de la etiqueta img
    $img_prev.src = objectURL;
    
    img_tweet();            
}

function loaded() {
    var URLactual = window.location;
    if (URLactual == "https://localhost/parcial/Index.php") {
        
        pageScroll();
    } else if (URLactual == "https://localhost/parcial/InkMisTweets.php") {
        
        pageScrollMyTuits();
    }
}

function pageScroll() {
    $(window).on("scroll", function() {
        var scrollHeight = $(document).height();
        var scrollPos    = $(window).height() + $(window).scrollTop();

        if((((scrollHeight - 250) >= scrollPos) / scrollHeight == 0) || (((scrollHeight - 300) >= scrollPos) / scrollHeight == 0) || 
            (((scrollHeight - 350) >= scrollPos) / scrollHeight == 0) || (((scrollHeight - 400) >= scrollPos) / scrollHeight == 0) || 
            (((scrollHeight - 450) >= scrollPos) / scrollHeight == 0) || (((scrollHeight - 500) >= scrollPos) / scrollHeight == 0)){
                
                var formulario = $('.last_tweet:last').serialize();

                const mensaje = new FormData();
                mensaje.append('formulario', formulario);
                
                $(window).off("scroll");
                $.ajax({
                    type: "POST",
                    url: "tuit.php",
                    data: mensaje,
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        setTimeout(function() {
                            $("#colummns_count").append(data);
                        pageScroll(); 
                        }, 500);
                    }
               });
        }
    });
}

function pageScrollMyTuits() {
    $(window).on("scroll", function() {
        var scrollHeight = $(document).height();
        var scrollPos    = $(window).height() + $(window).scrollTop();

        if((((scrollHeight - 250) >= scrollPos) / scrollHeight == 0) || (((scrollHeight - 300) >= scrollPos) / scrollHeight == 0) || 
            (((scrollHeight - 350) >= scrollPos) / scrollHeight == 0) || (((scrollHeight - 400) >= scrollPos) / scrollHeight == 0) || 
            (((scrollHeight - 450) >= scrollPos) / scrollHeight == 0) || (((scrollHeight - 500) >= scrollPos) / scrollHeight == 0)){
                
                var formulario = $('.last_tweet:last').serialize();

                const mensaje = new FormData();
                mensaje.append('formulario', formulario);
                
                $(window).off("scroll");
                $.ajax({
                    type: "POST",
                    url: "mytuit.php",
                    data: mensaje,
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        setTimeout(function() {
                            $("#colummns_count").append(data);
                        pageScroll(); 
                        }, 500);
                    }
               });
        }
    });
}

function change_label_Image(){
    document.getElementById('label_foto').innerHTML = document.getElementById('fullFoto').files[0].name;
}

function buttom_my_tweets(event){
    var id_buttom = event.target.id;
    if (document.getElementById("form_" + id_buttom).style.display == "block") {
        
        document.getElementById("form_" + id_buttom).style.display = "none";
    }
    else if(document.getElementById("form_" + id_buttom).style.display == "none"){

        document.getElementById("form_" + id_buttom).style.display = "block";
    }
}

function ajax_chat_update(){

    var req = new XMLHttpRequest();
    var req2 = new XMLHttpRequest();

    req.onreadystatechange = function() {
        
        if (req.readyState == 4 && req.status == 200) {
            document.getElementById("paquete_chats").innerHTML = req.responseText;
        }
    }

    req.open('GET', 'update_chats.php', true);
    req.send();

    req2.onreadystatechange = function() {
        
        if (req2.readyState == 4 && req2.status == 200) {
            document.getElementById("div_messages_chat_background").innerHTML = req2.responseText;
        }
    }

    req2.open('GET', 'chat.php', true);
    req2.send();
}

function search_void(){
    if ($(".search_chat").val() === "") {

        document.getElementById("onload_div").style.display = "none";
        document.getElementById("lista_users").style.display = "none";
        document.getElementById("paquete_chats").style.display = "block";
    }
}

function on_interval(){
    interval =   setInterval(function(){
                    ajax_chat_update();
                }, 4000)
}

function off_interval(){
    clearInterval(interval);
}

function keyUp(){
    
    if ($(".search_chat").val() !== "") {

        document.getElementById("lista_users").innerHTML = "";
        document.getElementById("paquete_chats").style.display = "none";
        document.getElementById("lista_users").style.display = "block";
        dataFilter();
    }
}

function dataFilter(){
    var formulario = $('.search_chat').serialize();
    var formulario = $('.search_chat').serialize();

    const mensaje = new FormData();
    mensaje.append('formulario', formulario); 
    
    $.ajax({
        type: "POST",
        url: "update_chats_filter.php",
        data: mensaje,
        async: true,
        contentType: false,
        processData: false,
        beforeSend: function(){
            document.getElementById("lista_users").style.display = "none";
            document.getElementById("onload_div").style.display = "flex";
        },
        success: function (data) {
            document.getElementById("lista_users").innerHTML = data;
        },
        complete: function ()
        {
            if ($(".search_chat").val() !== "") {

                document.getElementById("onload_div").style.display = "none";
                document.getElementById("lista_users").style.display = "block";
            }else{

                document.getElementById("onload_div").style.display = "none";
                document.getElementById("lista_users").style.display = "none";
            }
        },
    });
}

function changeChatUser(event){

    if (chat == 1) {
        
        var id_buttom_form = event.target.id;
        var formulario = $('#form_chat_'+id_buttom_form).serialize();

        const mensaje = new FormData();
        mensaje.append('formulario', formulario); 
        
        $.ajax({
            type: "POST",
            url: "change_user_chat.php",
            data: mensaje,
            async: true,
            contentType: false,
            processData: false,
            beforeSend: function(){
                chat = 0;
                document.getElementById("div_all_messages_Create").style.display = "none";
                document.getElementById("onload_chat_div").style.display = "flex";
                off_interval();
            },
            success: function (data) {
                document.getElementById("div_all_messages_Create").innerHTML = data;
            },
            complete: function ()
            {
                setTimeout(overFlow, 100);
                on_interval();
                document.getElementById("onload_chat_div").style.display = "none";
                document.getElementById("div_all_messages_Create").style.display = "block";
                chat = 1;
            },
        });
    }
}