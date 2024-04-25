self.addEventListener('fetch', function(event) {
  event.respondWith(
      caches.match(event.request).then(function(response) {
          return response || fetch(event.request).catch(function(e){
													var init = { "status" : 500 , "statusText" : "offline" };
													return new Response("",init);
												});
      })
  );
});