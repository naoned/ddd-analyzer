import React, { Component } from 'react'
import Graph from 'react-graph-vis'
import PropTypes from 'prop-types'
import { GraphConverter } from '../Converters/Graph.js'
import _ from 'lodash'

export class Viewer extends Component
{
    static propTypes = {
        options: PropTypes.object.isRequired,
    }

    state = {
        graph: {
            summary: {
                reportTime: null,
            },
            nodes: [],
            edges: [],
        }
    }
    
    componentDidMount() {
        const converter = new GraphConverter()
        
        fetch('/api/report')
        .then((response) => {
            if(response.status !== 200)
            {
                throw response.statusText
            }
            
            return response.json()
        })
        .then((report) => {
            this.setState({
              graph: converter.convertFromJson(report),
            })
        })
        .catch((error) => {
            console.error(error);
        });
    }
    
    render() {
        const { options } = this.props
        const { reportTime, hash } = this.state.graph.summary
        
        return (
            <div className="viewer">
                <ul className="info">
                    <li><strong>Report time:</strong> {reportTime !== null ? reportTime.toLocaleString() : '-'}</li>
                    <li><strong>Hash:</strong> <span title={hash}>{_.truncate(hash, { length: 8, omission: '' })}</span></li>
                </ul>
                <Graph graph={this.state.graph} options={options} />
            </div>
        )
    }
}
