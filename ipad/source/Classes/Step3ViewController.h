//
//  Step3ViewController.h
//  Redigone

#import <UIKit/UIKit.h>
#import "Reachability.h"
#import "FBConnect.h"


@class Step2ViewController;
@class ASIHTTPRequest;
@class ASIFormDataRequest;
@class PointView;
@class Thumb;

@interface Step3ViewController : UIViewController <UIActionSheetDelegate, FBSessionDelegate, FBRequestDelegate, FBDialogDelegate> {
	UISlider * th_slider;
	UIActivityIndicatorView * spinner;
	NSNumber * ratio;
	NSNumber * selectionCount;
	NSString * fileName;
	NSURL * url;
	ASIFormDataRequest * formRequest;
	ASIHTTPRequest * httpRequest;
	NSData * imageData;
	NSString * s;
	PointView * p;
	NSTimer * timer;
	UIImageView * imageView;
	UIAlertView * downloadAlert;
	Reachability * internetReachable;
	BOOL internetActive;
  IBOutlet UIBarButtonItem * barButtonItem;
  UIActionSheet * shareMenu;
  IBOutlet UISegmentedControl * segmentedControl;
}

@property (nonatomic, retain) IBOutlet UISlider * th_slider;
@property (nonatomic, retain) UIActivityIndicatorView * spinner;
@property (nonatomic, retain) NSNumber * ratio;
@property (nonatomic, retain) NSNumber * selectionCount;
@property (nonatomic, retain) NSString * fileName;
@property (nonatomic, retain) NSURL * url;
@property (nonatomic, retain) ASIFormDataRequest * formRequest;
@property (nonatomic, retain) ASIHTTPRequest * httpRequest;
@property (nonatomic, retain) NSData * imageData;
@property (nonatomic, retain) NSString * s;
@property (nonatomic, retain) PointView * p;
@property (nonatomic, retain) NSTimer * timer;
@property (nonatomic, retain) IBOutlet UIImageView * imageView;
@property (nonatomic, retain) UIAlertView * downloadAlert;
@property (nonatomic, retain) Reachability * internetReachable;
@property BOOL internetActive;
@property (nonatomic, retain) Facebook * facebook;
@property (nonatomic, retain) UIBarButtonItem * barButtonItem;
@property (nonatomic, retain) UIActionSheet * shareMenu;
@property (nonatomic, retain) IBOutlet UISegmentedControl * segmentedControl;

- (IBAction) startOver:(id)sender;
- (IBAction) sliderValueChanged:(id)sender;
- (void) uploadImage;
- (void) downloadImage;
- (void) getImageStatus:(NSTimer *)t;
- (void) requestFinished:(ASIHTTPRequest *)request;
- (void) requestFailed:(ASIHTTPRequest *)request;
- (void) showThumbImage;
- (Thumb *) getProcessedThumb;
- (void) generateThumbImage;
- (void) showDownloadError;
- (void) showDownloadSuccess;
- (IBAction) showShareMenu:(id) sender;
- (void) showXMLError;
- (void) showGeneralError;
- (void) showSessionError;
- (void) reachabilityChanged:(NSNotification *)notice;
- (void) configureForNetworkStatus:(NetworkStatus)status;
- (void) showConnectionError;
- (void) showTwitterLogin;
- (void) showFacebookLogin;
- (void) uploadFacebookPhoto;
- (void) validateAPIKey;
- (void) showOriginalThumbImage;
- (void) beforeAfter:(id)sender;
@end
