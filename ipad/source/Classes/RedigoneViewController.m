//
//  RedigoneViewController.m
//  Redigone


#import "RedigoneViewController.h"
#import "Step2ViewController.h"
#import "AboutViewController.h"
#import "GlobalData.h"


@implementation RedigoneViewController
@synthesize step2ViewController;
//@synthesize imageView;
@synthesize tmpImage;
@synthesize popoverController;
@synthesize webView;


/*
 *	Displays the About popup when a user clicks on the redigone.com text
 */
- (IBAction) showAbout:(id) sender {
	AboutViewController *aboutView = [[[AboutViewController alloc] initWithNibName:@"AboutViewController" bundle:[NSBundle mainBundle]] autorelease];
  
  
  //Use flip animation effect
  [UIView beginAnimations:@"animation" context:nil];
  [UIView setAnimationDuration: 0.7];
  [self.navigationController pushViewController:aboutView animated:NO];
  [UIView setAnimationTransition:UIViewAnimationTransitionFlipFromLeft forView:self.navigationController.view cache:NO]; 
  [UIView commitAnimations];
}


/*
 *	Displays the image picker controller from the Saved Photos Album
 */
-(IBAction) getPhoto:(id) sender {
	// dismiss any left over popovers here
	UIImagePickerController * picker = [[UIImagePickerController alloc] init]; 
	picker.sourceType = UIImagePickerControllerSourceTypePhotoLibrary; 
	picker.delegate = self; 
	
	UIPopoverController *popover = [[[UIPopoverController alloc] initWithContentViewController:picker] autorelease];
	
	self.popoverController = popover;          
	popoverController.delegate = self;
	
	CGRect myRect = CGRectMake(750, 19, 750, 19);

	//[popoverController presentPopoverFromBarButtonItem:sender permittedArrowDirections:UIPopoverArrowDirectionUp animated:YES];	
	
	[popoverController presentPopoverFromRect: myRect inView:self.view permittedArrowDirections:UIPopoverArrowDirectionUp animated:YES];
	[picker release];
	
}


/*
 *	Stores the image that was picked and proceeds to Step2
 */
-(void)imagePickerController:(UIImagePickerController *)picker didFinishPickingMediaWithInfo:(NSDictionary *)info {
	GlobalData * globalData = [GlobalData sharedInstance];
	
	[self.popoverController dismissPopoverAnimated:YES];

  tmpImage = [[UIImage alloc] autorelease];
	tmpImage = [info valueForKey:UIImagePickerControllerOriginalImage];
    
	//Store the image URL in globalData so we can POST it in Step3
	globalData.image_url = [info valueForKey:UIImagePickerControllerMediaURL];
	
	globalData.image = tmpImage;
	
	Step2ViewController *step2View = [[[Step2ViewController alloc] initWithNibName:@"Step2ViewController" bundle:[NSBundle mainBundle]] autorelease];
	
	/*
	 *	The following line creates an error for certain images
	 *	step2View.image = imageView.image;
	 *
	 *	CGBitmapContextCreate: unsupported parameter combination: 8 integer bits/component; 24 bits/pixel; 3-component colorspace; kCGImageAlphaNone; 896 bytes/row.
	 *	Which is explained in supported pixel formats
	 *	http://developer.apple.com/library/mac/#documentation/GraphicsImaging/Conceptual/drawingwithquartz2d/dq_context/dq_context.html#//apple_ref/doc/uid/TP30001066-CH203-TPXREF101
	 */
	
	//The workaround is to get a JPEG representation before we resize the image
	step2View.image = [UIImage imageWithData: UIImageJPEGRepresentation(tmpImage, 1.0)];	
    
	self.step2ViewController = step2View;
	[self.navigationController pushViewController:self.step2ViewController animated:YES];
}


/*
 *	The designated initializer.  Override if you create the controller programmatically and want to perform customization that is not appropriate for viewDidLoad.
 */

/*
- (id)initWithNibName:(NSString *)nibNameOrNil bundle:(NSBundle *)nibBundleOrNil {
    if ((self = [super initWithNibName:nibNameOrNil bundle:nibBundleOrNil])) {
        // Custom initialization
    }
    return self;
}
*/

/*
// Implement loadView to create a view hierarchy programmatically, without using a nib.
- (void)loadView {
}
*/



/*
 * Implement viewDidLoad to do additional setup after loading the view, typically from a nib.
 */
- (void)viewDidLoad {
	[super viewDidLoad];
  
  
  if([[UIApplication sharedApplication] respondsToSelector:@selector(setStatusBarHidden: withAnimation:)]) {
    [[UIApplication sharedApplication] setStatusBarHidden:NO withAnimation:UIStatusBarAnimationFade];
  }
  
  //Load slider in webview
    [webView loadRequest:[NSURLRequest requestWithURL:[NSURL fileURLWithPath:[[NSBundle mainBundle] pathForResource:@"step1" ofType:@"html"]isDirectory:NO]]];
  
	//Clear selected points, previous image and other globalData
	GlobalData * globalData = [GlobalData sharedInstance];
	[globalData resetData];		
}

// Override to allow orientations other than the default portrait orientation.
- (BOOL)shouldAutorotateToInterfaceOrientation:(UIInterfaceOrientation)interfaceOrientation {
    // Overriden to allow any orientation.
	if ((interfaceOrientation==UIInterfaceOrientationPortrait)||(interfaceOrientation==UIInterfaceOrientationPortraitUpsideDown)) {
		return NO;
	}
	
	if ((interfaceOrientation==UIInterfaceOrientationLandscapeLeft)||(interfaceOrientation==UIInterfaceOrientationLandscapeRight)) {
		return YES;
	}
	
	return NO;
}

- (void)didReceiveMemoryWarning {
	// Releases the view if it doesn't have a superview.
    [super didReceiveMemoryWarning];
	// Release any cached data, images, etc that aren't in use.
}

- (void)viewDidUnload {
	[super viewDidUnload];
	// Release any retained subviews of the main view.
  self.tmpImage = nil;
  self.webView = nil;
}


- (void)dealloc {
	[popoverController release];
	[step2ViewController release];
  [tmpImage release];
  [webView release];  
	[super dealloc];
}

@end
