#!/usr/bin/python

import os.path

from gimpfu import *

gettext.install("gimp20-python", gimp.locale_directory, unicode=True)

def rig( image , drawable , filename="" , treshold=50 , args="" ):
	print "Filename:'%s'"%(filename)
	print "Args: '%s'"%(args)
	if filename == "":
		print "No filename. Exiting"
		pdb.gimp_quit( 0 )
	elif not os.path.exists( filename ):
		print "File not found"
		pdb.gimp_quit( 0 )
	
	image = pdb.gimp_file_load( filename , filename )
	drawable = image.active_layer
	if args == "":
		pdb.gimp_rect_select( image , 0 , 0 , image.width , image.height , CHANNEL_OP_ADD , 0 , 0)
		pdb.gimp_edit_copy( drawable )
		floatselection = pdb.gimp_edit_paste( drawable , False )
		pdb.plug_in_red_eye_removal( image , floatselection , treshold , run_mode=1)
		image.flatten()
		drawable = image.active_layer
		pdb.gimp_file_save( image , drawable , filename , filename )
		pdb.gimp_image_delete( image )
		pdb.gimp_quit( 0 )
	else:
		args = args.split(' ')
		if len(args)%2 != 0:
			print "Incorect arguments number.Quit plugin"
			pdb.gimp_quit( 0 )
		for i in xrange(0,len(args),2):
			print "Type:'%s'"%args[i]
			if args[i] == "rect":
				params = args[i+1].split(',')
				if len(params) != 4:
					print "Rect: has incorrect param num"
				x1 = int(params[0])
				y1 = int(params[1])
				x2 = int(params[2])
				y2 = int(params[3])
				pdb.gimp_rect_select( image , x1 , y1 , x2-x1 , y2-y1 , CHANNEL_OP_ADD  ,0 , 0)
				print "Params: %d %d %d %d"%(x1 , y1 , x2 , y2)
			elif args[i] == "circ":
				params = args[i+1].split(',')
				if len(params) != 3:
					print "Circ: has incorrect param num"
				x = int(params[0])
				y = int(params[1])
				r = int(params[2])
				
				#we pass in 2*r from the api controller because the calculation 2*r does not work as a
				#parameter to gimp_ellipse_select. therefore the current 'r' is actually diameter
				
				#if GIMP is fixed so that the 2*r calculation work the fix will be
				#pdb.gimp_ellipse_select( image , x-(2*r)/2 , y-(2*r)/2 , 2*r , 2*r , CHANNEL_OP_ADD , True , 0 , 0 )
				
				pdb.gimp_ellipse_select( image , x-r/2 , y-r/2 , r , r , CHANNEL_OP_ADD , True , 0 , 0 )
				print "Params: %d %d %d"%( x , y , r )
			else:
				print "Unknown type.Quit plugin"
				pdb.gimp_quit( 0 )			
			pdb.gimp_edit_copy( drawable )
			floatselection = pdb.gimp_edit_paste( drawable , False )
			pdb.plug_in_red_eye_removal( image , floatselection , treshold , run_mode=1)
			#pdb.gimp_drawable_fill( floatselection , FOREGROUND_FILL )
			image.flatten()
			drawable = image.active_layer
	drawable = image.active_layer
	pdb.gimp_file_save( image , drawable , filename , filename )
	print "Treshold: %d"%( treshold )
	pdb.gimp_image_delete( image )
	print "Everything OK!"
	pdb.gimp_quit( 0 )


register(
    "python-rig",
    "Remove redeye",
    "Remove redeye",
    "Redigone",
    "Redigone",
    "2010",
    "<Image>/Filters/PyRig",
    "RGB*, GRAY*",
    [
    (PF_STRING,"filename","fn","img.jpg"),
    (PF_INT,"treshold","th",50),
    (PF_STRING, "args", "args", 0),
    ],
    [],
    rig)

main()