; Red iGone
; This file needs to be placed in Gimp's public script folder
;
(define (rig filename threshold)
(let* ((image (car (gimp-file-load RUN-NONINTERACTIVE filename filename)))
(drawable (car (gimp-image-get-active-layer image))))
(plug-in-red-eye-removal RUN-NONINTERACTIVE image drawable threshold)
(gimp-file-save RUN-NONINTERACTIVE image drawable filename filename)
(gimp-image-delete image)))