 <div class="container text-center mb-5">
     <button id="download-img" class="btn btn-primary mt-4">Download Discussion as Image</button>
 </div>

 <!-- html2canvas only -->
 <script src="<?= base_url('assets/html2canvas.min.js') ?>"></script>
 <script>
     document.getElementById('download-img').addEventListener('click', function() {
         const content = document.querySelector('.content');
         html2canvas(content, {
             scale: 2
         }).then(canvas => {
             const link = document.createElement('a');
             link.href = canvas.toDataURL('image/png');
             link.download = 'Discussion.png';
             document.body.appendChild(link);
             link.click();
             document.body.removeChild(link);
         });
     });
 </script>