var $ = require('jquery');

module.exports = function(){

    $(document).ready( function() {
      function addPost(post){
        $(post).hide().appendTo("#recipient").fadeIn(1000);
      }
       $(".load-more").click( function() {
          var type = $(this).attr("data-type");
          var limit = $(this).attr("data-limit");
          var page = $(this).attr("data-page");
          var count = $(this).attr("data-count");
          var maxPage = Math.ceil(count / limit);
          if (page >= maxPage) {
            return;
          };
          $.ajax({
             type : "get",
             contentType: "application/json; charset=utf-8",
             url : Infos.ajaxurl,
             data : {
                 action: 'load_more',
                 nonce: 1234,
                 type: type,
                 limit: limit,
                 page: page,
            },
             success: function(response) {
              $.each( $(response+' div'), function( i, el ) {
                setTimeout(function(){
                    addPost(el);
                },500 + ( i * 200 ));
              });
              $(".load-more").attr("data-page", parseInt(page) + 1)
             }
          })

       });

    });

}
