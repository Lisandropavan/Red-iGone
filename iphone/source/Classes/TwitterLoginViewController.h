//
//  TwitteRLoginViewController.h
//  Redigone


#import <UIKit/UIKit.h>
#import "KeychainItemWrapper.h"
#import "Reachability.h"

@class ASIHTTPRequest;
@class ASIFormDataRequest;

@interface TwitterLoginViewController : UIViewController <UITextFieldDelegate, UITextViewDelegate, UIScrollViewDelegate> {
	KeychainItemWrapper * wrapper;
	NSURL * url;
	NSString * s;
	UIAlertView * downloadAlert;
	ASIFormDataRequest * formRequest;
	NSData * imageData;
  BOOL keyboardIsShown;
  IBOutlet UIScrollView * scrollView;
  IBOutlet UITextView * textView;
  IBOutlet UILabel * uiLabel;
	IBOutlet UITextField * username;
	IBOutlet UITextField * password;
}

@property (nonatomic, retain) KeychainItemWrapper * wrapper;
@property (nonatomic, retain) NSURL * url;
@property (nonatomic, retain) NSString * s;
@property (nonatomic, retain) UIAlertView * downloadAlert;
@property (nonatomic, retain) ASIFormDataRequest * formRequest;
@property (nonatomic, retain) NSData * imageData;
@property BOOL keyboardIsShown;
@property (nonatomic, retain) UIScrollView * scrollView;
@property (nonatomic, retain) UITextView * textView;
@property (nonatomic, retain) UILabel * uiLabel;
@property (nonatomic, retain) UITextField * username;
@property (nonatomic, retain) UITextField * password;
@end
