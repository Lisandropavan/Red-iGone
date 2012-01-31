//
//  RedigoneViewController.h
//  Redigone

#import <UIKit/UIKit.h>
@class Step2ViewController;
@class AboutViewController;

@interface RedigoneViewController : UIViewController <UIImagePickerControllerDelegate, UIPopoverControllerDelegate, UINavigationControllerDelegate> {
	
  Step2ViewController * step2ViewController;
  UIImage * tmpImage;
  IBOutlet UIWebView *webView;
}

@property (nonatomic, retain) Step2ViewController * step2ViewController;
@property (nonatomic, retain) UIImage * tmpImage;
@property (nonatomic, retain) UIWebView *webView;

- (IBAction) getPhoto:(id) sender;
- (IBAction) showAbout:(id) sender;

@end

