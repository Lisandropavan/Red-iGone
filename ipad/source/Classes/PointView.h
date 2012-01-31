//
//  PointView.h
//  Redigone


#import <UIKit/UIKit.h>


@interface PointView : UIView {
	CGPoint startLocation;
	CGFloat radius;
	int pointName;
	BOOL selected;
}

@property CGPoint startLocation;
@property CGFloat radius;
@property int pointName;
@property BOOL selected;

@end
