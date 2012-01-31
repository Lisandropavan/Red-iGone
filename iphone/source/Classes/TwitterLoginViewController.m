//
//  TwitteRLoginViewController.m
//  Redigone


#import "TwitterLoginViewController.h"
#import "GlobalData.h"
#import "ASIHTTPRequest.h"
#import "ASIFormDataRequest.h"
#import "ASICacheDelegate.h"
#import "Reachability.h"
#import "XMLParser.h"

#define BARBUTTON(TITLE, SELECTOR) 	[[[UIBarButtonItem alloc] initWithTitle:TITLE style:UIBarButtonItemStylePlain target:self action:SELECTOR] autorelease]

#define TWITPIC_URL @"http://twitpic.com/api/uploadAndPost"
#define TWITPIC_POST_FILENAME @"redigone.jpg"
#define TWITPIC_IN_PROGRESS_TITLE @"Uploading to Twitter..."
#define TWITPIC_FINISHED_TITLE @"Upload Success" 
#define TWITPIC_FINISHED_MESSAGE @"Your photo was successfully posted to Twitter"
#define TWITPIC_FINISHED_CANCEL_BUTTON @"OK"

#define TWITPIC_ERROR_TITLE @"Twitter Error"
#define TWITPIC_ERROR_CANCEL_BUTTON @"OK"

#define KEYBOARD_ANIMATION_DURATION 0.3

@implementation TwitterLoginViewController
@synthesize wrapper;
@synthesize url;
@synthesize s;
@synthesize downloadAlert;
@synthesize formRequest;
@synthesize imageData;
@synthesize keyboardIsShown;
@synthesize scrollView;
@synthesize textView;
@synthesize uiLabel;
@synthesize username;
@synthesize password;

/*
 *	Called after a HTTP request is finished
 *	Processes status codes and takes appropriate action if needed
 */
- (void) requestFinished:(ASIHTTPRequest *)request {
  GlobalData * globalData = [GlobalData sharedInstance];  
	
	// Use when fetching text data
	//neded for debug only
  //NSString *responseString = [request responseString];

  //needed for debug only
  /*if (responseString != nil) {
    NSLog(@"Response string: %@", responseString);
  }*/
  
	/*
  int statusCode = [request responseStatusCode];
	NSString *statusMessage = [request responseStatusMessage];		
	
	NSLog(@"Status code: %i", statusCode);
	NSLog(@"Status message: %@", statusMessage);
  */
  
  //Contains XML response from Twitpic
  NSData * responseData = [request responseData];	
		
  //Parse Twitpic XML response
  XMLParser * parser = [[XMLParser alloc] autorelease];
  [parser parseXMLData: responseData];
  
  if (globalData.twitpicUpload == true) {
  
    if (self.downloadAlert.visible) {
      [self.downloadAlert dismissWithClickedButtonIndex:0 animated:NO];
    
      self.downloadAlert = [[[UIAlertView alloc] initWithTitle:nil message: TWITPIC_FINISHED_MESSAGE delegate:self cancelButtonTitle:TWITPIC_FINISHED_CANCEL_BUTTON otherButtonTitles: nil] autorelease];
      [self.downloadAlert show];	
    }
    
    [self.parentViewController dismissModalViewControllerAnimated:YES];
  } else {
        
    if (self.downloadAlert.visible) {
      [self.downloadAlert dismissWithClickedButtonIndex:0 animated:NO];
    }
      
    self.downloadAlert = [[[UIAlertView alloc] initWithTitle:TWITPIC_ERROR_TITLE message: globalData.twitpicResponse delegate:self cancelButtonTitle:TWITPIC_ERROR_CANCEL_BUTTON otherButtonTitles: nil] autorelease];
    [self.downloadAlert show];	
    }    
}


- (void) upload: (id) sender {
  GlobalData * globalData = [GlobalData sharedInstance];

  // recover username and password and save it
  NSString *uname = [username text];
  NSString *pword = [password text];

  if (uname) [self.wrapper setObject:uname forKey:(id)kSecAttrAccount];
  if (pword) [self.wrapper setObject:pword forKey:(id)kSecValueData];

  
  self.downloadAlert = [[[UIAlertView alloc] initWithTitle:TWITPIC_IN_PROGRESS_TITLE message:nil delegate:self cancelButtonTitle:nil otherButtonTitles: nil] autorelease];
  [self.downloadAlert show];
  
  // Create and add the activity indicator
  UIActivityIndicatorView *aiv = [[UIActivityIndicatorView alloc] initWithActivityIndicatorStyle:UIActivityIndicatorViewStyleWhiteLarge];
  aiv.center = CGPointMake(self.downloadAlert.bounds.size.width / 2.0f, self.downloadAlert.bounds.size.height - 40.0f);
  [aiv startAnimating];
  [self.downloadAlert addSubview:aiv];
  [aiv release];
  
  UIImage * downloaded_image = [[[UIImage alloc] initWithData: [NSData dataWithContentsOfURL : globalData.current_download_url]] autorelease];
  
	self.s = [NSString string];
	
	self.url = [NSURL URLWithString:TWITPIC_URL];	
	self.formRequest = [[[ASIFormDataRequest alloc] initWithURL:self.url] autorelease];
	
  [self.formRequest setNumberOfTimesToRetryOnTimeout:3];
  
	[self.formRequest setPostValue:uname forKey:@"username"];
	[self.formRequest setPostValue:pword forKey:@"password"];	
  [self.formRequest setPostValue:self.textView.text forKey:@"message"];
  
  self.imageData = UIImageJPEGRepresentation(downloaded_image, 0.5);
	[self.formRequest addData:self.imageData withFileName:TWITPIC_POST_FILENAME andContentType:@"image/jpeg" forKey:@"media"];
	
	[self.formRequest setDelegate:self];
	[self.formRequest startAsynchronous];
}


- (void) dismissCancel: (id) sender {
	// dismiss but do not save
	[self.parentViewController dismissModalViewControllerAnimated:YES];
}


//Hide keyboard when user touches the Done button
- (BOOL)textField:(UITextField *)textField shouldChangeCharactersInRange:(NSRange)range replacementString:(NSString *)string {
  
  if ([string isEqualToString:@"\n"]) {
    [self.username resignFirstResponder];
    [self.password resignFirstResponder];
    
    return NO;
  }  else {
    return YES;
  }
}


- (void)textViewDidBeginEditing:(UITextView *)textView {
  [scrollView setContentOffset:CGPointMake(0, 150) animated:YES];
}

//Hide keyboard when user touches the Done button and
//also limit number of input chars to 115 + 25 for twitpic URL (added automatically to the message)
- (BOOL)textView:(UITextView *)textView shouldChangeTextInRange:(NSRange)range replacementText:(NSString *)text {

  if ([text isEqualToString:@"\n"]) {
    // Be sure to test for equality using the "isEqualToString" method
    [self.textView resignFirstResponder];
    
    // Return NO so that the final '\n' character doesn't get added
    return NO;
  }  
  
  
  NSString *newString = [self.textView.text stringByReplacingCharactersInRange:range withString:text];
  
  int strLength = [newString length];
  
  
  //Show counter as the users types
  if (strLength > 115) {
    self.uiLabel.text = @"0";
    return NO;
  } else {
    self.uiLabel.text = [NSString stringWithFormat:@"%d", 115-strLength];
    return YES;
  }
}


- (void)keyboardWillShow:(NSNotification *)n {

  if (keyboardIsShown) {
    return;
  }
  
  NSDictionary* userInfo = [n userInfo];
  
  // get the size of the keyboard
  NSValue* boundsValue = [userInfo objectForKey:UIKeyboardBoundsUserInfoKey];
  CGSize keyboardSize = [boundsValue CGRectValue].size;
  
  // resize the textView
  CGRect viewFrame = self.scrollView.frame;
  viewFrame.size.height = viewFrame.size.height - keyboardSize.height;
  
  [UIView beginAnimations:nil context:NULL];
  [UIView setAnimationBeginsFromCurrentState:YES];
  [UIView setAnimationDuration:KEYBOARD_ANIMATION_DURATION];
  [self.scrollView setFrame:viewFrame];
  [UIView commitAnimations];
  
  keyboardIsShown = YES;
}


- (void)keyboardWillHide:(NSNotification *)n {
  
  NSDictionary* userInfo = [n userInfo];
  
  // get the size of the keyboard
  NSValue* boundsValue = [userInfo objectForKey:UIKeyboardBoundsUserInfoKey];
  CGSize keyboardSize = [boundsValue CGRectValue].size;
  
  
  // resize the scrollview
  CGRect viewFrame = self.scrollView.frame;
  viewFrame.size.height = viewFrame.size.height + keyboardSize.height;
  
  [UIView beginAnimations:nil context:NULL];
  [UIView setAnimationBeginsFromCurrentState:YES];
  [UIView setAnimationDuration:KEYBOARD_ANIMATION_DURATION];
  [self.scrollView setFrame:viewFrame];
  [UIView commitAnimations];
  
  keyboardIsShown = NO;
}


- (void) viewDidLoad {
  //self.navigationController.navigationBar.barStyle = UIBarStyleBlackTranslucent;
  self.navigationController.navigationBar.barStyle = UIBarStyleBlackOpaque;
  self.navigationItem.leftBarButtonItem = BARBUTTON(@"Cancel", @selector(dismissCancel:));
  self.navigationItem.rightBarButtonItem = BARBUTTON(@"Upload", @selector(upload:));  
	
  
	self.wrapper = [[KeychainItemWrapper alloc] initWithIdentifier:@"Twitter" accessGroup:nil];
	[self.wrapper release];
	
	NSString *uname = [self.wrapper objectForKey:(id)kSecAttrAccount];
	NSString *pword = [self.wrapper objectForKey:(id)kSecValueData];
	
	if (uname) username.text = uname;
	if (pword) password.text = pword;

  

  // register for keyboard notifications
  [[NSNotificationCenter defaultCenter] addObserver:self 
                                           selector:@selector(keyboardWillShow:) 
                                               name:UIKeyboardWillShowNotification 
                                             object:self.view.window];
  // register for keyboard notifications
  [[NSNotificationCenter defaultCenter] addObserver:self 
                                           selector:@selector(keyboardWillHide:) 
                                               name:UIKeyboardWillHideNotification 
                                             object:self.view.window];
  
  keyboardIsShown = NO;
  
  //make contentSize bigger than scrollSize
  CGSize scrollContentSize = CGSizeMake(320, 350);
  self.scrollView.contentSize = scrollContentSize;
}


- (void)viewDidUnload {
	[super viewDidUnload];

  
  // unregister for keyboard notifications while not visible.
  [[NSNotificationCenter defaultCenter] removeObserver:self 
                                                  name:UIKeyboardWillShowNotification 
                                                object:nil]; 
  // unregister for keyboard notifications while not visible.
  [[NSNotificationCenter defaultCenter] removeObserver:self 
                                                  name:UIKeyboardWillHideNotification 
                                                object:nil];	
  
	// Release any retained subviews of the main view.
	username = nil;
	password = nil;
	wrapper = nil;
  url = nil;
  s = nil;
  downloadAlert = nil;
  formRequest = nil;
  imageData = nil;
  scrollView = nil;
  textView = nil;
  uiLabel = nil;
}

- (void) dealloc {
	[wrapper release];
  [url release];
  [s release];
  [downloadAlert release];
  [formRequest release];
  [imageData release];
  [scrollView release];
  [textView release];
  [uiLabel release];
  [username release];
  [password release];
	[super dealloc];
}
@end
