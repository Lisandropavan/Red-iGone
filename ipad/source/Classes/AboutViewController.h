//
//  AboutViewController.h
//  Redigone
//


#import <UIKit/UIKit.h>


@interface AboutViewController : UIViewController <UIWebViewDelegate> {
  IBOutlet UIWebView * webView;
}

@property (nonatomic, retain) UIWebView * webView;

- (IBAction) startOver:(id)sender;

@end
