/* Recommender
 Copyright (C) 2017 DISIT Lab http://www.disit.org - University of Florence 

 This program is free software: you can redistribute it and/or modify 
 it under the terms of the GNU Affero General Public License as 
 published by the Free Software Foundation, either version 3 of the 
 License, or (at your option) any later version. 

 This program is distributed in the hope that it will be useful, 
 but WITHOUT ANY WARRANTY; without even the implied warranty of 
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
 GNU Affero General Public License for more details. 

 You should have received a copy of the GNU Affero General Public License 
 along with this program.  If not, see <http://www.gnu.org/licenses/>. */
package smartcityrecommender;

import java.io.Serializable;

import org.apache.mahout.cf.taste.eval.IRStatistics;

import com.google.common.base.Preconditions;

public final class IRStatisticsImplCustom implements IRStatistics, Serializable {

    private final double precision;
    private final double recall;
    private final double fallOut;
    private final double ndcg;
    private final double reach;

    IRStatisticsImplCustom(double precision, double recall, double fallOut, double ndcg, double reach) {
        Preconditions.checkArgument(Double.isNaN(precision) || (precision >= 0.0 && precision <= 1.0),
                "Illegal precision: " + precision + ". Must be: 0.0 <= precision <= 1.0 or NaN");
        Preconditions.checkArgument(Double.isNaN(recall) || (recall >= 0.0 && recall <= 1.0),
                "Illegal recall: " + recall + ". Must be: 0.0 <= recall <= 1.0 or NaN");
        Preconditions.checkArgument(Double.isNaN(fallOut) || (fallOut >= 0.0 && fallOut <= 1.0),
                "Illegal fallOut: " + fallOut + ". Must be: 0.0 <= fallOut <= 1.0 or NaN");
        Preconditions.checkArgument(Double.isNaN(ndcg) || (ndcg >= 0.0 && ndcg <= 1.0),
                "Illegal nDCG: " + ndcg + ". Must be: 0.0 <= nDCG <= 1.0 or NaN");
        Preconditions.checkArgument(Double.isNaN(reach) || (reach >= 0.0 && reach <= 1.0),
                "Illegal reach: " + reach + ". Must be: 0.0 <= reach <= 1.0 or NaN");
        this.precision = precision;
        this.recall = recall;
        this.fallOut = fallOut;
        this.ndcg = ndcg;
        this.reach = reach;
    }

    @Override
    public double getPrecision() {
        return precision;
    }

    @Override
    public double getRecall() {
        return recall;
    }

    @Override
    public double getFallOut() {
        return fallOut;
    }

    @Override
    public double getF1Measure() {
        return getFNMeasure(1.0);
    }

    @Override
    public double getFNMeasure(double b) {
        double b2 = b * b;
        double sum = b2 * precision + recall;
        return sum == 0.0 ? Double.NaN : (1.0 + b2) * precision * recall / sum;
    }

    @Override
    public double getNormalizedDiscountedCumulativeGain() {
        return ndcg;
    }

    @Override
    public double getReach() {
        return reach;
    }

    @Override
    public String toString() {
        return "IRStatisticsImpl[precision:" + precision + ",recall:" + recall + ",fallOut:"
                + fallOut + ",nDCG:" + ndcg + ",reach:" + reach + ']';
    }

}
