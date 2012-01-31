//
//  Step3ViewController.m
//  Redigone

#import <QuartzCore/QuartzCore.h>
#import "Step3ViewController.h"
#import "Step2ViewController.h"
#import "ASIHTTPRequest.h"
#import "ASIFormDataRequest.h"
#import "ASICacheDelegate.h"
#import "GlobalData.h"
#import "PointView.h"
#import "XMLParser.h"
#import "Thumb.h"
#import "Reachability.h"
#import "TwitterLoginViewController.h"

#define DEBUG false

#define API_APP_NAME @"Red iGone for iPad"
#define API_APP_VERSION @"1.0.3"
#define API_URL_VALIDATE_KEY @"0.3/validate-api-key"
#define API_URL_UPLOAD @"0.3/image-upload-process"
#define API_URL_GENERATE_THUMB @"0.3/generate-thumb"
#define API_URL_IMAGE_STATUS @"0.3/image-status"
#define API_URL_DOWNLOAD_THUMB_FORMAT @"0.3/image-download?img=thumb_%@"
#define API_URL_DOWNLOAD_IMG_FORMAT @"0.3/image-download?img=%@"
#define API_UPLOAD_FILENAME @"rig_iPad.jpg"

#define API_MAX_RETRIES 1

#define CONNECTION_ERROR_TITLE @"Cannot Connect to the Server" 
#define CONNECTION_ERROR_MESSAGE @"You must connect to a Wi-Fi\n or cellular data network\n to process the image." 
#define CONNECTION_ERROR_CANCEL_BUTTON @"OK"

#define SHARE_ERROR_TITLE @"Error Saving Photo" 
#define SHARE_ERROR_MESSAGE @"The photo is being processed and\n is not ready to be saved" 
#define SHARE_ERROR_CANCEL_BUTTON @"OK"

#define SAVING_ERROR_TITLE @"Error Saving Photo" 
#define SAVING_ERROR_MESSAGE @"The photo is being processed and\n is not ready to be saved" 
#define SAVING_ERROR_CANCEL_BUTTON @"OK"

#define SAVING_IN_PROGRESS_TITLE @"Saving photo..."
#define SAVING_IN_PROGRESS_MESSAGE @"Please wait"

#define SAVING_FINISHED_TITLE @"Finished Saving Photo" 
#define SAVING_FINISHED_MESSAGE @"The photo was saved to\nyour photo album" 
#define SAVING_FINISHED_CANCEL_BUTTON @"OK"

#define SHARE_MENU_TITLE @"Share your image"
#define SHARE_MENU_OPTION_FACEBOOK @"Facebook"
#define SHARE_MENU_OPTION_TWITTER @"Twitter"
#define SHARE_MENU_OPTION_SAVE @"Save to Photo Album"
#define SHARE_MENU_OPTION_CANCEL @"Cancel"

#define PROCESSING_ERROR_TITLE @"Error processing image"
#define PROCESSING_ERROR_CANCEL_BUTTON @"OK"

#define SESSION_ERROR_TITLE @"Session Expired" 
#define SESSION_ERROR_MSG @"Please start over again"
#define SESSION_ERROR_CANCEL_BUTTON @"OK"

#define ERROR_CODE_MESSAGE @"Oops. Looks like we couldn't contact the photo processing server (error code: %d)"

#define FACEBOOK_APPID @"186381921410129"

#define FACEBOOK_POST_SUCCESS_TITLE @"Upload Complete"
#define FACEBOOK_POST_SUCCESS_MESSAGE @"Your photo was successfully uploaded to the album Red iGone Photos"
#define FACEBOOK_POST_SUCCESS_CANCEL_BUTTON @"OK"

#define FACEBOOK_GENERAL_ERROR_TITLE @"Facebook General Error"
#define FACEBOOK_GENERAL_ERROR_MESSAGE @"Photo was not posted to Facebook"
#define FACEBOOK_GENERAL_ERROR_CANCEL_BUTTON @"OK"

#define SPINNER_POSTION_X 351
#define SPINNER_POSITION_Y 395

@implementation Step3ViewController
@synthesize th_slider;
@synthesize spinner;
@synthesize ratio;
@synthesize selectionCount;
@synthesize fileName;
@synthesize url;
@synthesize httpRequest;
@synthesize formRequest;
@synthesize imageData;
@synthesize s;
@synthesize p;
@synthesize timer;
@synthesize imageView;
@synthesize downloadAlert;
@synthesize internetReachable;
@synthesize internetActive;
@synthesize facebook;
@synthesize barButtonItem;
@synthesize shareMenu;
@synthesize segmentedControl;

/*
 * Display sharing
 */

- (void) actionSheet:(UIActionSheet *)actionSheet clickedButtonAtIndex:(NSInteger)buttonIndex {
  
  switch (buttonIndex) {
      
    case 0:
      [self showFacebookLogin];      
      break;
      
      
    case 1:
      [self showTwitterLogin];
      break;
      
      
    case 2:
      [self downloadImage];
      break;
  }
  
  [actionSheet release];
}

/*
 *	Show Facebook authorize and login form
 */

- (void) showFacebookLogin {
  self.facebook = [[Facebook alloc] initWithAppId:FACEBOOK_APPID];
  [self.facebook authorize:[NSArray arrayWithObjects: @"publish_stream", nil] delegate:self];
}


/*
 * This function processes the URL the Facebook application or Safari used to
 * open your application during a single sign-on flow.
 */

- (BOOL)application:(UIApplication *)application handleOpenURL:(NSURL *) fburl {  
  return [self.facebook handleOpenURL:fburl];
}


/*
 * Uploads photo to Facebook
 */

- (void) uploadFacebookPhoto {
  
  GlobalData * globalData = [GlobalData sharedInstance];
  
  NSString * path = [globalData.current_download_url absoluteString];
  NSURL * url2 = [NSURL URLWithString:path];
  NSData * data = [NSData dataWithContentsOfURL:url2];
  UIImage * img = [[[UIImage alloc] initWithData:data] autorelease];
  
  NSMutableDictionary * params = [NSMutableDictionary dictionaryWithObjectsAndKeys:
                                  img, @"picture",
                                  nil];
  
  [facebook requestWithMethodName: @"photos.upload"
                        andParams: params
                    andHttpMethod: @"POST"
                      andDelegate: self];
}


/*
 * Called when the user has logged in successfully.
 */
- (void)fbDidLogin {
  
  [self uploadFacebookPhoto];
  
  /*
   *  Use below to post to wall with photo as URL
   */
  /*GlobalData * globalData = [GlobalData sharedInstance];  
   
   NSMutableDictionary* params = [NSMutableDictionary dictionaryWithObjectsAndKeys:
   FACEBOOK_APPID, @"app_id",
   //@"http://developers.facebook.com/docs/reference/dialogs/", @"link",
   [globalData.current_download_url absoluteString], @"picture",
   //@"Facebook Dialogs", @"name",
   //@"Reference Documentation", @"caption",
   //@"Dialogs provide a simple, consistent interface for apps to interact with users.", @"description",
   //@"Facebook Dialogs are so easy!",  @"message",
   nil];
   
   [self.facebook dialog:@"feed" andParams:params andDelegate:self];
   */
  
}

/**
 * Called when the user canceled the authorization dialog.
 */
-(void)fbDidNotLogin:(BOOL)cancelled {
  /*self.downloadAlert = [[[UIAlertView alloc] initWithTitle:FACEBOOK_LOGIN_ERROR_TITLE
   message:FACEBOOK_LOGIN_ERROR_MESSAGE
   delegate:self
   cancelButtonTitle:FACEBOOK_LOGIN_ERROR_CANCEL_BUTTON
   otherButtonTitles: nil] autorelease];
   [self.downloadAlert show];
   */
}

////////////////////////////////////////////////////////////////////////////////
// FBDialogDelegate

/**
 * Called when a UIServer Dialog successfully return.
 */
- (void)dialogDidComplete:(FBDialog *)dialog {
}



////////////////////////////////////////////////////////////////////////////////
// FBRequestDelegate

/**
 * Called when the Facebook API request has returned a response. This callback
 * gives you access to the raw response. It's called before
 * (void)request:(FBRequest *)request didLoad:(id)result,
 * which is passed the parsed response object.
 */
- (void)request:(FBRequest *)request didReceiveResponse:(NSURLResponse *)response {
  //NSLog(@"received response");
}

/**
 * Called when a request returns and its response has been parsed into
 * an object. The resulting object may be a dictionary, an array, a string,
 * or a number, depending on the format of the API response. If you need access
 * to the raw response, use:
 *
 * (void)request:(FBRequest *)request
 *      didReceiveResponse:(NSURLResponse *)response
 */
- (void)request:(FBRequest *)request didLoad:(id)result {
  /*
   if ([result isKindOfClass:[NSArray class]]) {
   result = [result objectAtIndex:0];
   }
   if ([result objectForKey:@"owner"]) {
   //[self.label setText:@"Photo upload Success"];
   NSLog(@"Photo upload Success");    
   } else {
   //[self.label setText:[result objectForKey:@"name"]];
   NSLog(@"didLoad result: %@", [result objectForKey:@"name"]);    
   }
   */
  
  self.downloadAlert = [[[UIAlertView alloc] initWithTitle:nil
                                                   message:FACEBOOK_POST_SUCCESS_MESSAGE
                                                  delegate:self
                                         cancelButtonTitle:FACEBOOK_POST_SUCCESS_CANCEL_BUTTON
                                         otherButtonTitles: nil] autorelease];
  [self.downloadAlert show];  
};

/**
 * Called when an error prevents the Facebook API request from completing
 * successfully.
 */
- (void)request:(FBRequest *)request didFailWithError:(NSError *)error {
  //[self.label setText:[error localizedDescription]];
  //NSLog(@"didFailWithError: %@", [error localizedDescription]);
  self.downloadAlert = [[[UIAlertView alloc] initWithTitle:FACEBOOK_GENERAL_ERROR_TITLE
                                                   message:FACEBOOK_GENERAL_ERROR_MESSAGE
                                                  delegate:self
                                         cancelButtonTitle:FACEBOOK_GENERAL_ERROR_CANCEL_BUTTON
                                         otherButtonTitles: nil] autorelease];
  [self.downloadAlert show];  
};


/*
 * Display share menu, but only if the photo has finished processing
 */
-(IBAction) showShareMenu: (UIBarButtonItem *) item {
  GlobalData * globalData = [GlobalData sharedInstance];
  
  self.downloadAlert = nil;
  
  if (globalData.current_download_url == nil) {
    self.downloadAlert = [[[UIAlertView alloc] initWithTitle:SHARE_ERROR_TITLE
                                                     message:SHARE_ERROR_MESSAGE
                                                    delegate:self
                                           cancelButtonTitle:SHARE_ERROR_CANCEL_BUTTON
                                           otherButtonTitles: nil] autorelease];
    [self.downloadAlert show];
    
  } else {
    
    if (self.shareMenu.visible == NO) {
    
      self.shareMenu = [[UIActionSheet alloc]
                           initWithTitle: SHARE_MENU_TITLE
                           delegate:self
                           cancelButtonTitle:SHARE_MENU_OPTION_CANCEL
                           destructiveButtonTitle:nil
                           otherButtonTitles:SHARE_MENU_OPTION_FACEBOOK, SHARE_MENU_OPTION_TWITTER, SHARE_MENU_OPTION_SAVE, nil];
    

      [self.shareMenu showFromBarButtonItem: barButtonItem animated:YES]; 
    }
  }
}


/*
 *	Show Twitter login credentials form
 */

- (void) showTwitterLogin {
	TwitterLoginViewController *tlvc = [[[TwitterLoginViewController alloc] init] autorelease];
	tlvc.modalTransitionStyle = UIModalTransitionStyleFlipHorizontal;
	UINavigationController *nav = [[[UINavigationController alloc] initWithRootViewController:tlvc] autorelease];
	[self.navigationController presentModalViewController:nav animated:YES];    
}


/*
 *	Re-calculates the threshold to fit the fixed values
 *	when the threshold slider changes
 */
- (IBAction) sliderValueChanged:(UISlider *)sender {
	if (sender.value < 12.5) {
		[self.th_slider setValue:0];
	} else if (sender.value >= 12.5 && sender.value < 37.5 ) {
		[self.th_slider setValue:25];
	} else if(sender.value >= 37.5 && sender.value < 62.5) {
		[self.th_slider setValue:50];
	} else if(sender.value >= 62.5 && sender.value < 87.5) {
		[self.th_slider setValue:75];
	} else if(sender.value >= 87.5) {
		[self.th_slider setValue:100];
	}
	
	self.imageView.image = nil;
	[self.imageView.layer setBorderWidth: 0.0];
	[self.spinner startAnimating];
	
	[self generateThumbImage];
}

/*
 * Validates the API key and gets a session_id
 */
- (void) validateAPIKey {
  GlobalData * globalData = [GlobalData sharedInstance];
  
  self.url = [NSURL URLWithString:API_URL_VALIDATE_KEY relativeToURL: globalData.secure_base_url];
  
  self.formRequest = [[[ASIFormDataRequest alloc] initWithURL:self.url] autorelease];
  
  [self.formRequest setNumberOfTimesToRetryOnTimeout:3];
  
  [self.formRequest setPostValue:globalData.api_key forKey:@"key"];
  
  [self.formRequest setDelegate:self];
	[self.formRequest startSynchronous];
}


/*
 *	Uploads the selected image to the API server
 */
- (void) uploadImage {
	GlobalData * globalData = [GlobalData sharedInstance];

  globalData.current_function = @"uploadImage";
  
  if (globalData.session_id == nil) {
    [self validateAPIKey];
  }
  
	self.s = [NSString string];	  
  
	self.url = [NSURL URLWithString:API_URL_UPLOAD relativeToURL: globalData.secure_base_url];	
	self.selectionCount = [NSNumber numberWithInt:[globalData.selectedPoints count]];
	self.fileName = API_UPLOAD_FILENAME;
	
	self.formRequest = [[[ASIFormDataRequest alloc] initWithURL:self.url] autorelease];
  
  [self.formRequest setNumberOfTimesToRetryOnTimeout:3];  

  [self.formRequest setPostValue:globalData.session_id forKey:@"session_id"];
	[self.formRequest setPostValue:[[UIDevice currentDevice] uniqueIdentifier] forKey:@"device_id"];
	[self.formRequest setPostValue:[[UIDevice currentDevice] name] forKey:@"device_name"];
	[self.formRequest setPostValue:[[UIDevice currentDevice] systemName] forKey:@"device_systemName"];
	[self.formRequest setPostValue:[[UIDevice currentDevice] systemVersion] forKey:@"device_systemVersion"];
	[self.formRequest setPostValue:[[UIDevice currentDevice] model] forKey:@"device_model"];
	[self.formRequest setPostValue:[[UIDevice currentDevice] localizedModel] forKey:@"device_localizedModel"];
	[self.formRequest setPostValue:API_APP_NAME forKey:@"app_name"];
	[self.formRequest setPostValue:API_APP_VERSION forKey:@"app_version"];
	[self.formRequest setPostValue:self.selectionCount forKey:@"num_selections"];
	[self.formRequest setPostValue:[NSNumber numberWithFloat: [self.th_slider value]] forKey:@"threshold"];	
  if (DEBUG) {
    NSLog(@"Original width: %f", globalData.original_width);
    NSLog(@"Original height: %f", globalData.original_height);
    NSLog(@"Adaptive size x: %f", globalData.adaptiveSizeX);
    NSLog(@"Adaptive size y: %f", globalData.adaptiveSizeY);
    NSLog(@"Size: ");
    NSString * size = [NSString stringWithFormat:@"%ix%i", (int)globalData.original_width, (int)globalData.original_height];
    NSLog(@"%@",size);
  }
  
  if (globalData.original_width > globalData.adaptiveSizeX || globalData.original_height > globalData.adaptiveSizeY) {
    [self.formRequest setPostValue:globalData.adaptiveResize forKey:@"resize"];
  } else {
    [self.formRequest setPostValue:[NSString stringWithFormat:@"%ix%i", (int)globalData.original_width, (int)globalData.original_height] forKey:@"resize"];
  }
	
	if (self.selectionCount > 0) {
		for (int i=0; i<[globalData.selectedPoints count]; i++) {
			self.p = [globalData.selectedPoints objectAtIndex: i];
			//Calculate corresponding coordinates in the original size image
			CGFloat xc = (self.p.center.x - globalData.imageViewUpperLeft.x)*globalData.ratio;
			CGFloat yc = (self.p.center.y - globalData.imageViewUpperLeft.y)*globalData.ratio;
			CGFloat r = self.p.radius * (globalData.ratio);
			
			//NSLog(@"selection radius %.3f", self.p.radius);
			//NSLog(@"globalData.imageViewUpperLeft.x %.3f", globalData.imageViewUpperLeft.x);
			//NSLog(@"globalData.imageViewUpperLeft.y %.3f", globalData.imageViewUpperLeft.y);
			//NSLog(@"self.p.center.x %.3f", self.p.center.x);
			//NSLog(@"self.p.center.y %.3f", self.p.center.y);
			//NSLog(@"coords %.3f %.3f %.3f", xc, yc, r);
			//NSLog(@"sent to server: %@", self.s);
			
			self.s = [NSString stringWithFormat: @"circ,%.0f,%.0f,%.0f", xc, yc, r];
			[self.formRequest setPostValue: self.s forKey:[NSString stringWithFormat:@"selection%i",i]];
			self.p = nil;
		}
	}
	
	self.imageData = UIImageJPEGRepresentation(globalData.image, 0.5);
	[self.formRequest addData:self.imageData withFileName:self.fileName andContentType:@"image/jpeg" forKey:@"filename"];
	
	//self.imageData = UIImagePNGRepresentation(globalData.image);
	//[request addData:self.imageData withFileName:self.fileName andContentType:@"image/png" forKey:@"filename"];
	
	[self.formRequest setDelegate:self];
	[self.formRequest startAsynchronous];

}


/*
 *	Downloads the selected image to the API server
 */
- (void) downloadImage {
	GlobalData * globalData = [GlobalData sharedInstance];
	//NSLog(@"Download URL: %@",globalData.current_download_url);
	self.downloadAlert = nil;
	
		self.downloadAlert = [[[UIAlertView alloc] initWithTitle:SAVING_IN_PROGRESS_TITLE message:SAVING_IN_PROGRESS_MESSAGE delegate:self cancelButtonTitle:nil otherButtonTitles: nil] autorelease];
		[self.downloadAlert show];
		
		// Create and add the activity indicator
		/*UIActivityIndicatorView *aiv = [[UIActivityIndicatorView alloc] initWithActivityIndicatorStyle:UIActivityIndicatorViewStyleWhiteLarge];
		aiv.center = CGPointMake(self.downloadAlert.bounds.size.width / 2.0f, self.downloadAlert.bounds.size.height - 40.0f);
		[aiv startAnimating];
		
    [self.downloadAlert addSubview:aiv];
		[aiv release];
		*/
		UIImage * downloaded_image = [[[UIImage alloc] initWithData: [NSData dataWithContentsOfURL : globalData.current_download_url]] autorelease];
		UIImageWriteToSavedPhotosAlbum(downloaded_image, self, @selector(image:didFinishSavingWithError:contextInfo:), nil);
}


/*
 *	Called when the image has been saved, displays success or error message
 */
- (void) image: (UIImage *) image didFinishSavingWithError: (NSError *) error contextInfo: (void *) contextInfo {
	GlobalData * globalData = [GlobalData sharedInstance];
	globalData.saveStatusCode = [error code];
	
	if ([error code] != 0) {
		[self showDownloadError];
	} else {		
		[self showDownloadSuccess];
	}
}


/*
 *	Displays save photo error messsage popup
 */
- (void) showDownloadError {
	GlobalData * globalData = [GlobalData sharedInstance];
	
	if (self.downloadAlert.visible) {
		[self.downloadAlert dismissWithClickedButtonIndex:0 animated:NO];
	}
	
	NSString * msg = [NSString stringWithFormat:ERROR_CODE_MESSAGE, globalData.saveStatusCode];
	
	self.downloadAlert = [[[UIAlertView alloc] initWithTitle:SAVING_ERROR_TITLE message:msg delegate:self cancelButtonTitle:SAVING_ERROR_CANCEL_BUTTON otherButtonTitles: nil] autorelease];
	[self.downloadAlert show];	
}


/*
 *	Displays save photo success message popup
 */
- (void) showDownloadSuccess {
	if (self.downloadAlert.visible) {
		[self.downloadAlert dismissWithClickedButtonIndex:0 animated:NO];
	}
	self.downloadAlert = [[[UIAlertView alloc] initWithTitle:SAVING_FINISHED_TITLE message:SAVING_FINISHED_MESSAGE delegate:self cancelButtonTitle:SAVING_FINISHED_CANCEL_BUTTON otherButtonTitles: nil] autorelease];
	[self.downloadAlert show];	
}


/*
 *	Displays XML error message popup
 */
- (void) showXMLError {
	GlobalData * globalData = [GlobalData sharedInstance];
	if (self.downloadAlert.visible) {
		[self.downloadAlert dismissWithClickedButtonIndex:0 animated:NO];
	}
	
	NSString * msg = [NSString stringWithFormat:ERROR_CODE_MESSAGE, globalData.statusCode];
	
	self.downloadAlert = [[[UIAlertView alloc] initWithTitle:PROCESSING_ERROR_TITLE message:msg delegate:self cancelButtonTitle:PROCESSING_ERROR_CANCEL_BUTTON otherButtonTitles: nil] autorelease];
	[self.downloadAlert show];
}


/*
 *	Displays General Error error message popup
 *	Note: Error code 0 could mean unable to reach server (DNS error)
 */
- (void) showGeneralError {	
	GlobalData * globalData = [GlobalData sharedInstance];
	
	if (self.downloadAlert.visible) {
		[self.downloadAlert dismissWithClickedButtonIndex:0 animated:NO];
	}
	
		//NSLog(@"showGeneralError");
		NSString * msg = [NSString stringWithFormat:ERROR_CODE_MESSAGE, globalData.statusCode];
		self.downloadAlert = [[[UIAlertView alloc] initWithTitle:PROCESSING_ERROR_TITLE message:msg delegate:self cancelButtonTitle:PROCESSING_ERROR_CANCEL_BUTTON otherButtonTitles: nil] autorelease];
	
	[self.downloadAlert show];
}


/*
 *	Displays Session Expired error message popup
 */
- (void) showSessionError {	
	if (self.downloadAlert.visible) {
		[self.downloadAlert dismissWithClickedButtonIndex:0 animated:NO];
	}
	
	//NSLog(@"showSessionExpiredError");
	NSString * msg = SESSION_ERROR_MSG;
	self.downloadAlert = [[[UIAlertView alloc] initWithTitle:SESSION_ERROR_TITLE message:msg delegate:self cancelButtonTitle:SESSION_ERROR_CANCEL_BUTTON otherButtonTitles: nil] autorelease];
	
	[self.downloadAlert show];
}

/*
 *	Displays Internet connection error message popup
 */
- (void) showConnectionError {
	if (self.downloadAlert.visible) {
		[self.downloadAlert dismissWithClickedButtonIndex:0 animated:NO];
	}

	//NSLog(@"showConnectionError");
	self.downloadAlert = [[[UIAlertView alloc] initWithTitle:CONNECTION_ERROR_TITLE message:CONNECTION_ERROR_MESSAGE
													 delegate:self cancelButtonTitle:CONNECTION_ERROR_CANCEL_BUTTON otherButtonTitles: nil] autorelease];
	[self.downloadAlert show];
}


/*
 *	On unrecoverable error, sends the user back to Step1
 */
- (void)alertView:(UIAlertView *)alertView didDismissWithButtonIndex:(NSInteger)buttonIndex {
	GlobalData * globalData = [GlobalData sharedInstance];
	if (globalData.unrecoverableError == YES || self.internetActive == NO) {
		[super.navigationController popToRootViewControllerAnimated:YES];
	}
}


/*
 *	Generates a new thumb image on the server
 */
- (void) generateThumbImage {
	GlobalData * globalData = [GlobalData sharedInstance];

  globalData.current_function = @"generateThumbImage";  

  if (globalData.session_id == nil) {
    [self validateAPIKey];
  }
  
	globalData.queue_th = nil;
	self.th_slider.userInteractionEnabled = NO;
	
	//NSLog(@"Slider value %0.f",[self.th_slider value]);
															
	for (Thumb * currentThumb in globalData.thumbImages) {
		if ([currentThumb.th isEqualToString: [NSString stringWithFormat:@"%.0f", [self.th_slider value]]]) {
			globalData.queue_th = currentThumb.th;
		}
	}

	if (globalData.queue_th != nil) {
        
    if (DEBUG) {
      NSLog(@"Thumb exists in globaldata");
    }    

		[self showThumbImage];
    
	} else {

    if (DEBUG) {
      NSLog(@"Thumb does not exist in globaldata");
		}

    self.segmentedControl.selectedSegmentIndex = 1;    
    [self.segmentedControl setEnabled:NO forSegmentAtIndex:0];
    [self.segmentedControl setEnabled:NO forSegmentAtIndex:1];
    
		globalData.current_download_url = nil;
		
		self.s = [NSString string];
		self.url = [NSURL URLWithString:API_URL_GENERATE_THUMB relativeToURL: globalData.secure_base_url];
		
		self.selectionCount = [NSNumber numberWithInt:[globalData.selectedPoints count]];
		
    self.formRequest = [[[ASIFormDataRequest alloc] initWithURL:self.url] autorelease];

    [self.formRequest setNumberOfTimesToRetryOnTimeout:3];

    [self.formRequest setPostValue:globalData.session_id forKey:@"session_id"];    
		[self.formRequest setPostValue:[[UIDevice currentDevice] uniqueIdentifier] forKey:@"device_id"];
		[self.formRequest setPostValue:self.selectionCount forKey:@"num_selections"];
		[self.formRequest setPostValue:[NSNumber numberWithFloat: [self.th_slider value]] forKey:@"threshold"];		
		[self.formRequest setPostValue:globalData.original_name forKey:@"name"];
		[self.formRequest setPostValue:globalData.extension forKey:@"extension"];
    
    if (DEBUG) {
      NSLog(@"Original width: %f", globalData.original_width);
      NSLog(@"Original height: %f", globalData.original_height);
      NSLog(@"Adaptive size x: %f", globalData.adaptiveSizeX);
      NSLog(@"Adaptive size y: %f", globalData.adaptiveSizeY);
      NSLog(@"Size: ");
      NSString * size = [NSString stringWithFormat:@"%ix%i", (int)globalData.original_width, (int)globalData.original_height];
      NSLog(@"%@",size);
    }
    
    if (globalData.original_width > globalData.adaptiveSizeX || globalData.original_height > globalData.adaptiveSizeY) {
      [self.formRequest setPostValue:globalData.adaptiveResize forKey:@"resize"];
		} else {
      [self.formRequest setPostValue:[NSString stringWithFormat:@"%ix%i", (int)globalData.original_width, (int)globalData.original_height] forKey:@"resize"];
    }
    
		if (self.selectionCount > 0) {
			for (int i=0; i<[globalData.selectedPoints count]; i++) {
				self.p = [globalData.selectedPoints objectAtIndex: i];
				//Calculate corresponding coordinates in the original size image
				CGFloat xc = (self.p.center.x - globalData.imageViewUpperLeft.x)*globalData.ratio;
				CGFloat yc = (self.p.center.y - globalData.imageViewUpperLeft.y)*globalData.ratio;
				CGFloat r = self.p.radius * (globalData.ratio);
				
				self.s = [NSString stringWithFormat: @"circ,%.0f,%.0f,%.0f", xc, yc, r];
				[self.formRequest setPostValue: self.s forKey:[NSString stringWithFormat:@"selection%i",i]];
				self.p = nil;
			}
		}
		
		[self.formRequest setDelegate:self];
		[self.formRequest startAsynchronous];
	}	
}


/*
 *	Checks the status of selected image on the API server
 */
- (void) getImageStatus:(NSTimer *)t {
  
  if (DEBUG) {
    NSLog(@"In function: getImageStatus");
  }
  
	GlobalData * globalData = [GlobalData sharedInstance];

  globalData.current_function = @"getImageStatus";

	if (globalData.session_id == nil) {
    [self validateAPIKey];
  }	  
  
	if (globalData.queue_id != nil) {
		self.url = [NSURL URLWithString:API_URL_IMAGE_STATUS relativeToURL: globalData.base_url];
		
    self.formRequest = [[[ASIFormDataRequest alloc] initWithURL:self.url] autorelease];

    [self.formRequest setNumberOfTimesToRetryOnTimeout:2];    
    
    [self.formRequest setPostValue:globalData.session_id forKey:@"session_id"];
    
		[self.formRequest setPostValue:globalData.queue_id forKey:@"id"];
		
		[self.formRequest setDelegate:self];
		[self.formRequest startAsynchronous];
	}	else {
		[t invalidate];
		self.timer = nil;

    if(DEBUG) {
      NSLog(@"globalData.processed_image_name: %@", globalData.processed_image_name);
      NSLog(@"globalData.processed_image_th: %@", globalData.processed_image_th);
    }		
    
		if (globalData.processed_image_name != nil && globalData.processed_image_th != nil) {
			Thumb * t = [[Thumb alloc] autorelease];
			t.name = globalData.processed_image_name;
			t.th = globalData.processed_image_th;
			[globalData.thumbImages addObject:t];
			[self showThumbImage];
		}
	}
}


/*
 *	Retrieves processed thumb image from global data object
 */
- (Thumb *) getProcessedThumb {
	GlobalData * globalData = [GlobalData sharedInstance];
	
	for (Thumb * currentThumb in globalData.thumbImages) {
    if([currentThumb.th isEqualToString: globalData.queue_th]) {
			return currentThumb;
		}
	}
	
	return [[Thumb alloc] autorelease];
}


/*
 *	Displays the thumb image
 */
- (void) showThumbImage {
	GlobalData * globalData = [GlobalData sharedInstance];
  
  if (DEBUG) {
    NSLog(@"In function showThumbImage");
  }

  [self.segmentedControl setEnabled:YES forSegmentAtIndex:0];
  [self.segmentedControl setEnabled:YES forSegmentAtIndex:1];
  self.segmentedControl.selectedSegmentIndex = 1;
  
  globalData.image_expiration = [[[NSDate alloc] init] autorelease];
  
	self.th_slider.userInteractionEnabled = YES;

  
	Thumb * t = [self getProcessedThumb];

	if (t.name != nil) {
		globalData.queue_id = nil;
		globalData.queue_th = nil;
		globalData.queue_name = nil;

		NSString * u = [[[NSString alloc] initWithFormat:API_URL_DOWNLOAD_THUMB_FORMAT, t.name] autorelease];
		NSString * cdu = [[[NSString alloc] initWithFormat:API_URL_DOWNLOAD_IMG_FORMAT, t.name] autorelease];
		
		NSURL * thumb_url = [NSURL URLWithString: u relativeToURL: globalData.base_url];
		globalData.current_download_url = [NSURL URLWithString: cdu relativeToURL: globalData.base_url];
		
    if (DEBUG) {
      NSLog(@"Thumb URL: %@", [thumb_url absoluteURL]);
      NSLog(@"Download URL: %@", [globalData.current_download_url absoluteURL]); 
      NSLog(@"Hide spinner and show thumb image");
    }
		
		[self.spinner stopAnimating];
		
		UIImage * thumb_image = [[UIImage alloc]initWithData: [NSData dataWithContentsOfURL: thumb_url]];
		
		if(self.imageView == nil) {
			self.imageView = [[[UIImageView alloc] initWithFrame: CGRectMake(0, 0, thumb_image.size.width, thumb_image.size.height)] autorelease];
		}
		self.imageView.userInteractionEnabled = NO;
		self.imageView.hidden = NO;
		self.imageView.image = thumb_image;
		
		//Reposition the view
		CGPoint newPoint = CGPointMake(globalData.imagePositionX, globalData.imagePositionY);
		self.imageView.center = newPoint;
		
		[self.imageView.layer setBorderColor: [[UIColor whiteColor] CGColor]];
		[self.imageView.layer setBorderWidth: 2.0];

    
		//Add view to current view
		[self.view addSubview: self.imageView];
		[self.view bringSubviewToFront: self.imageView];
		[thumb_image release];
	}
}

/*
 *	Displays the original thumb image
 */
- (void) showOriginalThumbImage {
	GlobalData * globalData = [GlobalData sharedInstance];
  
  if (DEBUG) {
    NSLog(@"In function showOriginalThumbImage");
  }
  
  NSString * u = [[[NSString alloc] initWithFormat:API_URL_DOWNLOAD_IMG_FORMAT, globalData.original_thumb_name] autorelease];
  NSURL * thumb_url = [NSURL URLWithString: u relativeToURL: globalData.base_url];
  UIImage * thumb_image = [[UIImage alloc]initWithData: [NSData dataWithContentsOfURL: thumb_url]];

  if (DEBUG) {
    NSLog(@"Thumb URL: %@", [thumb_url absoluteURL]);
    //NSLog(@"Download URL: %@", [globalData.current_download_url absoluteURL]); 
    NSLog(@"Hide spinner and show thumb image");
  }  
  
  if(self.imageView == nil) {
    self.imageView = [[[UIImageView alloc] initWithFrame: CGRectMake(0, 0, thumb_image.size.width, thumb_image.size.height)] autorelease];
  }
  self.imageView.userInteractionEnabled = NO;
  self.imageView.hidden = NO;
  self.imageView.image = thumb_image;
  [self.segmentedControl setEnabled:YES forSegmentAtIndex:0];
  [self.segmentedControl setEnabled:YES forSegmentAtIndex:1];
  self.segmentedControl.selectedSegmentIndex = 0;

  //Reposition the view
  CGPoint newPoint = CGPointMake(globalData.imagePositionX, globalData.imagePositionY);
  self.imageView.center = newPoint;
  
  [self.imageView.layer setBorderColor: [[UIColor whiteColor] CGColor]];
  [self.imageView.layer setBorderWidth: 2.0];
  
  //Add view to current view
  [self.view addSubview: self.imageView];
  [self.view bringSubviewToFront: self.imageView];
  [thumb_image release];
}

/*
 *	Run when segmented control is touched
 */
- (void) beforeAfter:(id)sender {
	//UISegmentedControl *segmentedControl = (UISegmentedControl *)sender;
  //self.segmentedControl = (UISegmentedControl *) sender;
  
  if ([self.segmentedControl selectedSegmentIndex] == 0) {
    if (DEBUG) {
      NSLog(@"Segmented Index 0 touched");
    }
    [self showOriginalThumbImage];
  } else if ([self.segmentedControl selectedSegmentIndex] == 1) {
    if (DEBUG) {
      NSLog(@"Segmented Index 1 touched");
    }    
    [self generateThumbImage];
  }
} 

/*
 *	Called after a HTTP request is finished
 *	Processes status codes and takes appropriate action if needed
 */
- (void) requestFinished:(ASIHTTPRequest *)request {
  if (DEBUG) {
    NSLog(@"In method: requestFinished");
  }
  
	GlobalData * globalData = [GlobalData sharedInstance];
	
	// Use when fetching text data
	//neded for debug only
	NSString *responseString = [request responseString];
		
	// Use when fetching binary data
	NSData * responseData = [request responseData];
		
	int statusCode = [request responseStatusCode];
	//NSString *statusMessage = [request responseStatusMessage];		
	
	//NSLog(@"Status code: %i", statusCode);
	//NSLog(@"Status message: %@", statusMessage);
	
	if (statusCode == 200 || statusCode == 400) {
    
		//needed for debug only
		if (responseString != nil) {
			if (DEBUG) {
				NSLog(@"Response string: %@", responseString);
			}
		}
		
		XMLParser * parser = [[XMLParser alloc] autorelease];
		[parser parseXMLData: responseData];
		
		//show general error message if request returned xml error message
		if (globalData.unrecoverableError == YES) {
			globalData.statusCode = statusCode;
			if (self.internetActive == YES) {
				[self showXMLError];
			}
		}

 		//Notify all instances of Step3ViewController to run getImageStatus
    if (DEBUG) {
      NSLog(@"globalData queue_id: %@", globalData.queue_id);
      if (self.timer == nil) {
        NSLog(@"Timer is nil");
      } else {
        NSLog(@"Timer is not nil");
      }
    }		
    

    //Check if we have a valid session_id, otherwise run the same function
    //that was called again
    if (globalData.session_id == nil) {
      
      if(globalData.num_retries < API_MAX_RETRIES) {
        if (DEBUG) {
          NSLog(@"Invalid session_id, reached maximum retries");
        }
        globalData.unrecoverableError = YES;
        globalData.queue_id = nil;
        [self showSessionError];
        
      } else {
        if (DEBUG) {
          NSLog(@"Invalid session_id, retrying...");
        }
        globalData.num_retries++;
        
        if (globalData.current_function == @"uploadImage") {
          [self uploadImage];
        } else if (globalData.current_function == @"generateThumbImage") {
          [self generateThumbImage];
        }
        
      }
    } else if (globalData.queue_id != nil && self.timer == nil) {
      
      if (self.timer == nil) {
        //Setup the notification center for image status updates
        
        if (DEBUG) {
          NSLog(@"Re-initated notification center in requestFinished");
        }
        
        [[NSNotificationCenter defaultCenter] addObserver:self selector:@selector(receiveSelectionNotification:) name:@"ImageStatusChangedNotification" object:nil];
      }
      
      if (DEBUG) {
        NSLog(@"Posted image status changed notification");
      }
      [[NSNotificationCenter defaultCenter] postNotificationName:@"ImageStatusChangedNotification" object:self];	
    }
    
	} else {
		globalData.statusCode = [request responseStatusCode];
		globalData.unrecoverableError = YES;

		if (self.internetActive == YES) {
			[self showGeneralError];
		}
	}
	
	self.httpRequest = nil;
	self.formRequest = nil;
	self.url = nil;
	
}


/*
 *	Called if HTTP request failed, displays appropriate error message
 */
- (void) requestFailed:(ASIHTTPRequest *)request {
  if (DEBUG) {
    NSLog(@"In method: requestFailed");
	}
  
	GlobalData * globalData = [GlobalData sharedInstance];
	
	globalData.statusCode = [request responseStatusCode];
	globalData.unrecoverableError = YES;
	
	if (self.internetActive == YES) {
		[self showGeneralError];
	}
	
  if (DEBUG) {
    int statusCode = [request responseStatusCode];
    NSString *statusMessage = [request responseStatusMessage];
    NSLog(@"ASIHTTPRequest failed");
    NSLog(@"Status code: %i", statusCode);
    NSLog(@"Status message: %@", statusMessage);
  }
}


/*
 *	Called when network reachability is changed
 */
- (void) reachabilityChanged:(NSNotification *)notice {
	// called after network status changes
	
	// get Reachability instance from notification
	Reachability * curReach = [notice object];
	NetworkStatus networkStatus = [curReach currentReachabilityStatus];
	
	[self configureForNetworkStatus:networkStatus];
}


/*
 *	Sets the network status flags
 */
- (void)configureForNetworkStatus:(NetworkStatus)status {
	
	switch (status) {
			
		case NotReachable: {
			if(self.internetActive == YES) {
				//NSLog(@"No Internet connection available. Step3");
				self.internetActive = NO;
				[self showConnectionError];
			}
			break;
		}
			
		case ReachableViaWiFi: {
			//NSLog(@"Internet connection via WIFI. Step3");
			self.internetActive = YES;
			break;
		}
			
		case ReachableViaWWAN: {
			//NSLog(@"Internet connection via WWAN. Step3");
			self.internetActive = YES;
			break;
		}
	}
}


/*
 *	The designated initializer.  Override if you create the controller programmatically and want to perform customization that is not appropriate for viewDidLoad.
 */
- (id)initWithNibName:(NSString *)nibNameOrNil bundle:(NSBundle *)nibBundleOrNil {
    if ((self = [super initWithNibName:nibNameOrNil bundle:nibBundleOrNil])) {
			// Custom initialization
    }
    return self;
}


/*
 * Implement viewDidLoad to do additional setup after loading the view, typically from a nib.
 */
- (void)viewDidLoad {
  if (DEBUG) {
    NSLog(@"In method: viewDidLoad");
  }
	
	//Notification center for internet connection
	[[NSNotificationCenter defaultCenter] addObserver:self
																					 selector:@selector(reachabilityChanged:)
																							 name:kReachabilityChangedNotification
																						 object:nil];
	
	
	self.internetReachable = [[Reachability reachabilityForInternetConnection] retain];
	
	//get first status
	NetworkStatus siteNetworkStatus = [self.internetReachable currentReachabilityStatus];
	
	[self configureForNetworkStatus:siteNetworkStatus];
	[self.internetReachable startNotifier];	

	//Position the th slider vertically
	self.th_slider.transform = CGAffineTransformRotate(self.th_slider.transform, 270.0/180*M_PI); 	

	//Disable th slider until the first thumb has been generated
	self.th_slider.userInteractionEnabled = NO;
	
	spinner = [[UIActivityIndicatorView alloc] initWithActivityIndicatorStyle:UIActivityIndicatorViewStyleWhiteLarge];
	[spinner setCenter:CGPointMake(SPINNER_POSTION_X, SPINNER_POSITION_Y)];
	[spinner startAnimating];
	[self.view addSubview:spinner];
	
  [self.segmentedControl addTarget:self
	                     action:@selector(beforeAfter:)
	           forControlEvents:UIControlEventValueChanged];
  
	//Post form data to RiG server for processing
	if (self.internetActive == YES) {
		[self uploadImage];
	}
	
	self.timer = nil;
  
	[super viewDidLoad];
}


/*
 *	Issues an API call to check the image status every 3 seconds when an
 *	image change notification is received
 */
- (void) receiveSelectionNotification:(NSNotification *) notification {
	if ([[notification name] isEqualToString:@"ImageStatusChangedNotification"]) {
		if (DEBUG) {
      NSLog(@"Received the ImageStatusChangedNotification");
		}
    
    GlobalData * globalData = [GlobalData sharedInstance];
		
		if (globalData.queue_id != nil) {
      if (DEBUG) {
        NSLog(@"Initiating timer");
      }
			self.timer = [NSTimer scheduledTimerWithTimeInterval: 3.0 target: self selector: @selector(getImageStatus:) userInfo: nil repeats: YES];
		}
	}
}


/*
 * Go back to Step1
 */
- (IBAction)startOver:(id)sender {
	GlobalData * globalData = [GlobalData sharedInstance];
	[globalData resetData];
  
  [self.shareMenu dismissWithClickedButtonIndex:0 animated:NO];
  
	[super.navigationController popToRootViewControllerAnimated:YES];
}


/*
 *	Override to allow orientations other than the default portrait orientation.
 */

- (BOOL)shouldAutorotateToInterfaceOrientation:(UIInterfaceOrientation)interfaceOrientation {
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

- (void) viewWillDisappear:(BOOL)animated {
  
	[[NSNotificationCenter defaultCenter] removeObserver:self];
	if (self.downloadAlert.visible) {
		[self.downloadAlert dismissWithClickedButtonIndex:0 animated:NO];
	}
  
}

- (void)viewDidUnload {	
	// Release any retained subviews of the main view.
	self.th_slider = nil;
	self.ratio = nil;
	self.selectionCount = nil;
	self.fileName = nil;
	self.url = nil;
	self.imageData = nil;
	self.s = nil;
	self.p = nil;
	self.timer = nil;
	self.formRequest = nil;
	self.httpRequest = nil;
	self.downloadAlert = nil;
  self.shareMenu = nil;
  self.segmentedControl = nil;
  
	[super viewDidUnload];
}


- (void)dealloc {
	[th_slider release];
	[ratio release];
	[selectionCount release];
	[fileName release];
	[url release];
	[imageData release];
	[s release];
	[p release];
	[timer release];
	[formRequest release];
	[httpRequest release];
	[downloadAlert release];
	[internetReachable release];
  [shareMenu release];
  [segmentedControl release];
  
	[super dealloc];
}

@end
