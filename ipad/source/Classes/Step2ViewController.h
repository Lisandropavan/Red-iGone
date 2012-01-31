//
//  Step2ViewController.h
//  Redigone


#import <UIKit/UIKit.h>
#import "UIImage+Resize.h"
#import "Reachability.h"
@class Step3ViewController;

@interface Step2ViewController : UIViewController <UITableViewDelegate, UITableViewDataSource> {
	UIImageView * imageView;
	UIImage * image;
	UISlider * slider;
	UITableView * tableView;
	Reachability * internetReachable;
	BOOL internetActive;
	Step3ViewController * step3ViewController;
}

@property (nonatomic, retain) IBOutlet UIImageView * imageView;
@property (nonatomic, retain) UIImage * image;
@property (nonatomic, retain) IBOutlet UISlider * slider;
@property (nonatomic, retain) IBOutlet UITableView * tableView;
@property (nonatomic, retain) Reachability * internetReachable;
@property BOOL internetActive;
@property (nonatomic, retain) Step3ViewController * step3ViewController;

- (IBAction) switchPage:(id)sender;
- (IBAction) sliderValueChanged:(id)sender;
- (void) reachabilityChanged:(NSNotification *)notice;
- (void) configureForNetworkStatus:(NetworkStatus)status;
- (IBAction) startOver:(id)sender;
- (void) clearCurrentSelections;

@end
